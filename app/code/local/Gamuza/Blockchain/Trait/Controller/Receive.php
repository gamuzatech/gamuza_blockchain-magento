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

require_once (Mage::getModuleDir ('lib', 'Gamuza_Blockchain') . DS . 'lib' . DS . 'phpqrcode' . DS . 'qrlib.php');

trait Gamuza_Blockchain_Trait_Controller_Receive
{
    /**
     * Retrieve customer session model object
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getSession ()
    {
        return Mage::getSingleton ('customer/session');
    }

    protected function _getHelper ()
    {
        return Mage::helper ('blockchain');
    }

    protected function _getOrder ()
    {
        $orderId = $this->getRequest ()->getParam ('order_id');

        return Mage::getModel ('sales/order')->load ($orderId);
    }

    protected function _updatePaymentAddress ($order, $transaction)
    {
        $order->getPayment ()->setData (Gamuza_Blockchain_Helper_Data::ORDER_PAYMENT_ATTRIBUTE_BLOCKCHAIN_ADDRESS, $transaction->getAddress ())->save ();
    }

    protected function _setQRBody ($address)
    {
        ob_start ();

        QRCode::png ($address, false, 'h', 6, 0, false);

        $result = base64_encode (ob_get_contents ());

        ob_end_clean ();

        return $this->getResponse ()->setBody ("<img src='data:image/png;base64,{$result}' />");
    }

    protected function _throwException ($message)
    {
        return $this->getResponse ()->setHttpResponseCode (400)->setBody ($message);
    }

	public function addressAction ()
	{
        if (!$this->_validateFormKey ())
        {
            return $this->_throwException (Mage::helper ('blockchain')->__('Invalid form key. Please refresh the page.'));
        }

        $isAdmin = Mage::app ()->getStore ()->isAdmin ();

        if (!$isAdmin && !$this->_getSession ()->isLoggedIn ())
        {
            return $this->_throwException (Mage::helper ('blockchain')->__('Customer is not logged in.'));
        }

        $order = $this->_getOrder ();

        if (!$order || !$order->getId ())
        {
            return $this->_throwException (Mage::helper ('blockchain')->__('Order was not specified.'));
        }

        if (!$isAdmin && ($order->getCustomerId () != $this->_getSession ()->getCustomer ()->getId ()))
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

        if (!empty ($blockchainAddress))
        {
            return $this->_setQRBody ($blockchainAddress);
        }

        $collection = Mage::getModel ('blockchain/transaction')->getCollection ()
            ->addFieldToFilter ('order_id',    array ('eq' => $order->getId ()))
            ->addFieldToFilter ('customer_id', array ('eq' => $order->getCustomerId ()))
        ;

        $collection->getSelect ()->order ('entity_id DESC')->limit (1);

        $transaction = $collection->getFirstItem ();

        if ($transaction->getAddress ())
        {
            $this->_updatePaymentAddress ($order, $transaction);

            return $this->_setQRBody ($transaction->getAddress ());
        }
/*
        if (Mage::getStoreConfigFlag (Gamuza_Blockchain_Helper_Data::XML_PAYMENT_BLOCKCHAIN_KEEP_RETRIES))
        {
            $transaction = Mage::getModel ('blockchain/transaction');
        }
*/
        if (!$transaction->getId ())
        {
            $transaction->setCustomerId ($order->getCustomerId ())
                ->setOrderId ($order->getId ())
                ->setOrderIncrementId ($order->getIncrementId ())
                ->setCurrencyType ($order->getPayment ()->getCcType ())
                ->setAmount ($order->getPayment ()->getData (Gamuza_Blockchain_Helper_Data::ORDER_PAYMENT_ATTRIBUTE_BLOCKCHAIN_AMOUNT))
                ->setCreatedAt (date ('c'))
            ;
        }

        $transaction->setStatus (Gamuza_Blockchain_Helper_Data::STATUS_CREATED)
            ->setMessage (new Zend_Db_Expr ('NULL'))
            ->save ()
        ;

        $result = null;

        try
        {
            $secret = Mage::helper ('core')->encrypt ($order->getId ());

            $notificationUrl = Mage::getUrl ('blockchain/receive/notification', array ('_secure' => true, '_query' => array ('secret' => $secret)));

            $receiveAddressMethod = sprintf ("%s?key=%s&xpub=%s&gap_limit=%s&callback=%s",
                Gamuza_Blockchain_Helper_Data::API_RECEIVE_ADDRESS_METHOD,
                Mage::getStoreConfig (Gamuza_Blockchain_Helper_Data::XML_PAYMENT_BLOCKCHAIN_KEY),
                Mage::getStoreConfig (Gamuza_Blockchain_Helper_Data::XML_PAYMENT_BLOCKCHAIN_XPUB),
                Mage::getStoreConfig (Gamuza_Blockchain_Helper_Data::XML_PAYMENT_BLOCKCHAIN_GAP),
                urlencode ($notificationUrl)
            );

            $response = $this->_getHelper ()->api ($receiveAddressMethod);

            $transaction->setAddress ($response->address)
                ->setIndex ($response->index)
                ->setCallback ($response->callback)
                ->setStatus (Gamuza_Blockchain_Helper_Data::STATUS_PENDING)
                ->setMessage (new Zend_Db_Expr ('NULL'))
                ->setUpdatedAt (date ('c'))
                ->save ()
            ;

            $this->_updatePaymentAddress ($order, $transaction);

            $result = $response->address;
        }
        catch (Exception $e)
        {
            $transaction->setStatus (Gamuza_Blockchain_Helper_Data::STATUS_ERROR)
                ->setMessage ($e->getMessage ())
                ->setUpdatedAt (date ('c'))
                ->save ()
            ;

            return $this->_throwException (Mage::helper ('blockchain')->__('Error fetching bitcoin address for payment. Please try again later.'));
        }

        $this->_setQRBody ($result);
	}

    protected function _invoiceOrder ($order, $transaction)
    {
        try
        {
            if (!$order->canInvoice ())
            {
                return $this->_throwException (Mage::helper ('blockchain')->__('Cannot create an invoice.'));
            }

            /**
             * Paid Status
             */
            $status = Mage::getStoreConfig ('payment/gamuza_blockchain_info/paid_status');

            $message = Mage::helper ('sales')->__('Approved the payment online.');

            $order->setState (Mage_Sales_Model_Order::STATE_NEW, $status, $message, false)
                ->save ()
            ;

            /**
             * Invoice
             */
            $invoice = Mage::getModel ('sales/service_order', $order)->prepareInvoice ();

            if (!$invoice->getTotalQty ())
            {
                return $this->_throwException (Mage::helper ('blockchain')->__('Cannot create an invoice without products.'));
            }

            $invoice->setRequestedCaptureCase (Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE)->register ();

            $invoice->getOrder ()->setIsInProcess (true);

            $transactionSave = Mage::getModel ('core/resource_transaction')
                ->addObject ($invoice)
                ->addObject ($invoice->getOrder ())
                ->save ();
            ;
        }
        catch (Mage_Core_Exception $e)
        {
            return $this->_throwException (Mage::helper ('blockchain')->__('INTERNAL ERROR: Cannot create an invoice.'));
        }

        $transaction->setStatus (Gamuza_Blockchain_Helper_Data::STATUS_PAID)
            ->setSyncedAt (date ('c'))
            ->save ()
        ;

        $comment = Mage::helper ('blockchain')->__('Payment approved via blockchain.info notification.');

        $invoice->sendEmail (true, $comment);

        $order->addStatusHistoryComment ($comment)->save ();

        $this->getResponse ()->setBody ('ok');

        return true;
    }
}

