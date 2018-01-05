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

class Gamuza_Blockchain_Model_Cron_Abstract
{
    protected $_apiKey = null;

    public function __construct ()
    {
        Mage::app ()->getTranslator ()->init (Mage_Core_Model_App_Area::AREA_ADMINHTML, true);

        $this->message ('+ Begin : ' . strftime ('%c'));

        static::_construct ();
    }

    public function _construct ()
    {
        $this->_apiKey = Mage::getStoreConfig (Gamuza_Blockchain_Helper_Data::XML_PAYMENT_BLOCKCHAIN_KEY);
    }

    public function __destruct ()
    {
        $this->message ('= End : ' . strftime ('%c'));
    }

    protected function getConfig ()
    {
        return Mage::getModel ('blockchain/config');
    }

    protected function getHelper ()
    {
        return Mage::helper ('blockchain');
    }

    protected function getStoreConfig ($key)
    {
        return $this->getHelper ()->getStoreConfig ($key);
    }

    protected function getCoreResource ()
    {
        return Mage::getSingleton ('core/resource');
    }

    protected function getReadConnection ()
    {
        return $this->getCoreResource ()->getConnection ('core_read');
    }

    protected function getWriteConnection ()
    {
        return $this->getCoreResource ()->getConnection ('core_write');
    }

    protected function message ($text)
    {
        Mage::log ($text, null, Gamuza_Blockchain_Helper_Data::LOG);
    }
}

