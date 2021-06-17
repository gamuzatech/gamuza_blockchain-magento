<?php
/**
 * @package     Gamuza_Blockchain
 * @description Bitcoin Crypto Currency Wallet
 * @copyright   Copyright (c) 2018 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Library General Public
 * License as published by the Free Software Foundation; either
 * version 2 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Library General Public License for more details.
 *
 * You should have received a copy of the GNU Library General Public
 * License along with this library; if not, write to the
 * Free Software Foundation, Inc., 51 Franklin St, Fifth Floor,
 * Boston, MA 02110-1301, USA.
 */

/**
 * See the AUTHORS file for a list of people on the Gamuza Team.
 * See the ChangeLog files for a list of changes.
 * These files are distributed with gamuza_blockchain-magento at http://github.com/gamuzatech/.
 */

class Gamuza_Blockchain_ReceiveController extends Mage_Core_Controller_Front_Action
{
    use Gamuza_Blockchain_Trait_Controller_Receive;

    protected $_notifyParams = array ('transaction_hash', 'address', 'confirmations', 'value', 'secret');
    protected $_blockParams  = array ('hash', 'confirmations', 'height', 'timestamp', 'size', 'secret');

    protected $_blockConfs  = Gamuza_Blockchain_Helper_Data::API_REQUEST_BLOCK_CONFIRMATIONS;
    protected $_blockHeight = null;

    public function _construct ()
    {
        $this->_blockConfs  = Mage::getStoreConfig (Gamuza_Blockchain_Helper_Data::XML_PAYMENT_BLOCKCHAIN_BLOCK_CONFS);
        $this->_blockHeight = Mage::getStoreConfig (Gamuza_Blockchain_Helper_Data::XML_PAYMENT_BLOCKCHAIN_BLOCK_HEIGHT);
    }

	public function notificationAction ()
	{
        $params = $this->getRequest ()->getParams ();

        foreach ($params as $_id => $_param)
        {
            if (!in_array ($_id, $this->_notifyParams))
            {
                unset ($params [$_id]);
            }
        }

        if (empty ($params))
        {
            return $this->_throwException (Mage::helper ('blockchain')->__('Invalid Notification Parameters.'));
        }

        extract ($params);

        if (!intval ($confirmations) || (intval ($confirmations) < intval (Mage::getStoreConfig ('payment/blockchain/confirmations'))))
        {
            return $this->_throwException (Mage::helper ('blockchain')->__('We are discarding unsafe %s confirmations.', $confirmations));
        }

        $orderId = intval (Mage::helper ('core')->decrypt ($secret));

        $collection = Mage::getModel ('sales/order')->getCollection ();

        $collection->getSelect ()->join (
            array ('transaction' => Mage::getSingleton ('core/resource')->getTableName ('gamuza_blockchain_transaction')),
            "main_table.entity_id = transaction.order_id AND transaction.order_id = '{$orderId}' AND transaction.address = '{$address}'",
            array ('transaction_id' => 'transaction.entity_id')
        );

        $order = $collection->getFirstItem ();

        if (!$order || !$order->getId ())
        {
            return $this->_throwException (Mage::helper ('blockchain')->__('Order was not specified.'));
        }

        $transaction = Mage::getModel ('blockchain/transaction')->load ($order->getTransactionId ());

        if (strcmp ($transaction->getStatus (), Gamuza_Blockchain_Helper_Data::STATUS_BALANCE))
        {
            return $this->_throwException (Mage::helper ('blockchain')->__('Invalid transaction status for payment action.'));
        }

        if ($order->getCustomerId () != $transaction->getCustomerId ())
        {
            return $this->_throwException (Mage::helper ('blockchain')->__('Order does not belong to the customer.'));
        }
/*
        $orderStatus = Mage::getStoreConfig (Gamuza_Blockchain_Helper_Data::XML_PAYMENT_BLOCKCHAIN_ORDER_STATUS);
        if (strcmp ($order->getStatus (), $orderStatus))
*/
        if (strcmp ($order->getState (), Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW))
        {
            return $this->_throwException (Mage::helper ('blockchain')->__('Invalid order status for payment action.'));
        }

        $blockchainAddress = $order->getPayment ()->getData (Gamuza_Blockchain_Helper_Data::ORDER_PAYMENT_ATTRIBUTE_BLOCKCHAIN_ADDRESS);

        if (strcmp ($blockchainAddress, $address))
        {
            return $this->_throwException (Mage::helper ('blockchain')->__('Invalid order payment for blockchain address.'));
        }

        $value_in_btc = $value / Gamuza_Blockchain_Helper_Data::SATOSHI;

        if (round ($value_in_btc, 8) != round ($order->getPayment ()->getData (Gamuza_Blockchain_Helper_Data::ORDER_PAYMENT_ATTRIBUTE_BLOCKCHAIN_AMOUNT), 8))
        {
            return $this->_throwException (Mage::helper ('blockchain')->__('Invalid value of the payment received.'));
        }

        $transaction->setHash ($transaction_hash)
            ->setConfirmations ($confirmations)
            ->setMessage (new Zend_Db_Expr ('NULL'))
            ->setUpdatedAt (date ('c'))
            ->save ()
        ;

        if (Mage::getStoreConfigFlag (Gamuza_Blockchain_Helper_Data::XML_PAYMENT_BLOCKCHAIN_USE_BLOCK))
        {
            $secret = Mage::helper ('core')->encrypt ($orderId);

            $notificationUrl = Mage::getUrl ('blockchain/receive/block', array ('_secure' => true, '_query' => array ('secret' => $secret)));

            $block = Mage::getModel ('blockchain/block')->load ($transaction->getId (), 'transaction_id');

            $block->setTransactionId ($transaction->getId ())
                ->setOrderId ($order->getId ())
                ->setCallback ($notificationUrl)
                ->setConfirmations ($this->_blockConfs)
                ->setHeight ($this->_blockHeight)
                ->setStatus (Gamuza_Blockchain_Helper_Data::STATUS_PENDING)
                ->setMessage (new Zend_Db_Expr ('NULL'))
                ->setCreatedAt (date ('c'))
                ->save ()
            ;

            $transaction->setStatus (Gamuza_Blockchain_Helper_Data::STATUS_BLOCK)
                ->save ()
            ;

            return $this->_throwException (Mage::helper ('blockchain')->__('We are waiting for block notification.'));
        }

        $this->_invoiceOrder ($order, $transaction);
	}

	public function blockAction ()
	{
        $params = $this->getRequest ()->getParams ();

        foreach ($params as $_id => $_param)
        {
            if (!in_array ($_id, $this->_blockParams))
            {
                unset ($params [$_id]);
            }
        }

        if (empty ($params))
        {
            return $this->_throwException (Mage::helper ('blockchain')->__('Invalid Notification Parameters.'));
        }

        extract ($params);

        if (!intval ($confirmations) || (intval ($confirmations) < intval (Mage::getStoreConfig ('payment/blockchain/block_confirmations'))))
        {
            return $this->_throwException (Mage::helper ('blockchain')->__('We are discarding unsafe %s confirmations.', $confirmations));
        }

        $orderId = intval (Mage::helper ('core')->decrypt ($secret));

        $collection = Mage::getModel ('sales/order')->getCollection ();

        $collection->getSelect ()->join (
            array ('block' => Mage::getSingleton ('core/resource')->getTableName ('gamuza_blockchain_block')),
            "main_table.entity_id = block.order_id AND block.order_id = '{$orderId}'",
            array ('block_id' => 'block.entity_id')
        );

        $order = $collection->getFirstItem ();

        if (!$order || !$order->getId ())
        {
            return $this->_throwException (Mage::helper ('blockchain')->__('Order was not specified.'));
        }

        $block = Mage::getModel ('blockchain/block')->load ($order->getBlockId ());

        if (strcmp ($block->getStatus (), Gamuza_Blockchain_Helper_Data::STATUS_BLOCK))
        {
            return $this->_throwException (Mage::helper ('blockchain')->__('Invalid block status for payment action.'));
        }

        $orderStatus = Mage::getStoreConfig (Gamuza_Blockchain_Helper_Data::XML_PAYMENT_BLOCKCHAIN_ORDER_STATUS);

        if (strcmp ($order->getStatus (), $orderStatus))
        {
            return $this->_throwException (Mage::helper ('blockchain')->__('Invalid order status for payment action.'));
        }

        $block->setHash ($hash)
            ->setConfirmations ($confirmations)
            ->setHeight ($height)
            ->setTimestamp ($timestamp)
            ->setSize ($size)
            ->setMessage (new Zend_Db_Expr ('NULL'))
            ->setUpdatedAt (date ('c'))
            ->save ()
        ;

        $transaction = Mage::getModel ('blockchain/transaction')->load ($block->getTransactionId ());

        $result = $this->_invoiceOrder ($order, $transaction);

        if ($result === true)
        {
            $block->setStatus (Gamuza_Blockchain_Helper_Data::STATUS_ADDED)
                ->setMessage (new Zend_Db_Expr ('NULL'))
                ->setSyncedAt (date ('c'))
                ->save ()
            ;
        }
	}
}

