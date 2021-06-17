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

class Gamuza_Blockchain_Model_Payment_Method_Blockchain extends Mage_Payment_Model_Method_Cc
{
    const CODE = 'gamuza_blockchain_info';

    protected $_code = self::CODE;

    protected $_canAuthorize = true;

    protected $_formBlockType = 'blockchain/payment_form_blockchain';
    protected $_infoBlockType = 'blockchain/payment_info_blockchain';

    /**
     * Validate payment method information object
     *
     * @param   Mage_Payment_Model_Info $info
     * @return  Mage_Payment_Model_Abstract
     */
    public function validate ()
    {
        /*
        * calling parent validate function
        */
        return Mage_Payment_Model_Method_Abstract::validate ();
    }

    /**
     * Order payment abstract method
     *
     * @param Varien_Object $payment
     * @param float $amount
     *
     * @return Mage_Payment_Model_Abstract
     */
    public function authorize(Varien_Object $payment, $amount)
    {
        parent::authorize($payment, $amount);

        $order = $payment->getOrder ();

        $transaction = Mage::getModel ('blockchain/transaction')
            ->setCustomerId ($order->getCustomerId ())
            ->setOrderId ($order->getId ())
            ->setOrderIncrementId ($order->getIncrementId ())
            ->setCurrencyType ($payment->getCcType ())
            ->setAmount ($payment->getData (Gamuza_Blockchain_Helper_Data::ORDER_PAYMENT_ATTRIBUTE_BLOCKCHAIN_AMOUNT))
            ->setCreatedAt (date ('c'))
            ->setStatus (Gamuza_Blockchain_Helper_Data::STATUS_CREATED)
            ->setMessage (new Zend_Db_Expr ('NULL'))
            ->save ()
        ;

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

            $response = Mage::helper ('blockchain')->api ($receiveAddressMethod);

            $transaction->setAddress ($response->address)
                ->setIndex ($response->index)
                ->setCallback ($response->callback)
                ->setStatus (Gamuza_Blockchain_Helper_Data::STATUS_PENDING)
                ->setMessage (new Zend_Db_Expr ('NULL'))
                ->setUpdatedAt (date ('c'))
                ->save ()
            ;

            $payment->setData (Gamuza_Blockchain_Helper_Data::ORDER_PAYMENT_ATTRIBUTE_BLOCKCHAIN_ADDRESS, $transaction->getAddress ())->save ();
        }
        catch (Exception $e)
        {
            $transaction->setStatus (Gamuza_Blockchain_Helper_Data::STATUS_ERROR)
                ->setMessage ($e->getMessage ())
                ->setUpdatedAt (date ('c'))
                ->save ()
            ;

            throw Mage::exception ('Gamuza_Blockchain', Mage::helper ('blockchain')->__('Error fetching bitcoin address for payment. Please try again later.'));
        }

        // $payment->setSkipOrderProcessing (true);
        $payment->setIsTransactionPending (true);

        return $this;
    }

    /**
     * Check whether there are CC types set in configuration
     *
     * @param Mage_Sales_Model_Quote|null $quote
     * @return bool
     */
    public function isAvailable ($quote = null)
    {
        $ccTypes = explode (',', $this->getConfigData ('cctypes'));
        $codes   = Mage::app ()->getStore ()->getAvailableCurrencyCodes (true);

        $intersect = count (array_intersect ($ccTypes, $codes)) > 0;

        return $intersect && parent::isAvailable ($quote);
    }
}

