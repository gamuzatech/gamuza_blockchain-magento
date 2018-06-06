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

class Gamuza_Blockchain_Block_Payment_Info_Blockchain extends Mage_Payment_Block_Info
{
    protected function _construct ()
    {
        parent::_construct ();

        $this->setTemplate ('gamuza/blockchain/payment/info/blockchain.phtml');
    }

    public function getAjaxLoaderUrl ()
    {
        $imageUrl = Mage::app ()->getStore ()->isAdmin () ? 'ajax-loader.gif' : 'opc-ajax-loader.gif';

        return $this->getSkinUrl ('images/' . $imageUrl);
    }

    protected function formatPrice ($currencyCode, $amount)
    {
        return sprintf ('%s %s', Mage::helper ('blockchain')->formatPrice ($currencyCode, $amount), $currencyCode);
    }

    protected function getQuote ()
    {
        return Mage::helper ('blockchain')->getQuote ();
    }

    public function getOrder ()
    {
        if (!strcmp (Mage::app ()->getRequest ()->getActionName (), 'email'))
        {
            return null;
        }

        return Mage::registry ('current_order');
    }

    public function getReceiveUrl ($orderId)
    {
        $receiveUrl = Mage::app ()->getStore ()->isAdmin () ? 'adminhtml/blockchain_receive/address' : 'blockchain/receive/address';

        $result = $this->getUrl ($receiveUrl, array(
            '_secure' => true, 'order_id' => $orderId,
            'form_key' => Mage::getSingleton ('core/session')->getFormKey ()
        ));

        return $result;
    }

    /**
     * Retrieve credit card type name
     *
     * @return string
     */
    public function getCcTypeName ()
    {
        $types = Mage::getSingleton ('blockchain/payment_config')->getCcTypes ();
        $ccType = $this->getInfo ()->getCcType ();

        if (isset ($types [$ccType]))
        {
            return $types [$ccType]['name'];
        }

        return (empty ($ccType)) ? Mage::helper ('payment')->__('N/A') : $ccType;
    }

    /**
     * Prepare credit card related payment info
     *
     * @param Varien_Object|array $transport
     * @return Varien_Object
     */
    protected function _prepareSpecificInformation ($transport = null)
    {
        if (null !== $this->_paymentSpecificInformation)
        {
            return $this->_paymentSpecificInformation;
        }

        $transport = parent::_prepareSpecificInformation ($transport);
        $data = array ();

        if ($ccType = $this->getCcTypeName ())
        {
            $data [Mage::helper ('blockchain')->__('Crypto Currency Type')] = $ccType;
        }

        $target = $this->getOrder () ? $this->getOrder () : $this->getQuote ();

        if ($target && $target->getId ())
        {
            $data [Mage::helper ('blockchain')->__('Order Base Grand Total')]   = $this->formatPrice (
                $target->getBaseCurrencyCode (), $target->getBaseGrandTotal ()
            );

            $data [Mage::helper ('blockchain')->__('Order Crypto Grand Total')] = $this->formatPrice (
                $this->getInfo ()->getCcType (), $this->getInfo ()->getData (Gamuza_Blockchain_Helper_Data::ORDER_PAYMENT_ATTRIBUTE_BLOCKCHAIN_AMOUNT)
            );
        }

        return $transport->setData (array_merge ($data, $transport->getData ()));
    }
}

