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

/**
 * Currency rate import model (From blockchain.info)
 *
 * @category   Mage
 * @package    Mage_Directory
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Gamuza_Blockchain_Model_Currency_Import_Blockchain extends Mage_Directory_Model_Currency_Import_Webservicex
{
    protected $_blockchainUrl = Gamuza_Blockchain_Helper_Data::API_REQUEST_CURRENCY_TO_BTC;

    public function __construct ()
    {
        parent::__construct ();

        $this->_currencyBase = Mage::getStoreConfig ('currency/options/base');
    }

    protected function _convert ($currencyFrom, $currencyTo, $retry = 0)
    {
        if (strcmp ($currencyFrom, $this->_currencyBase) || !in_array ($currencyTo, array ($this->_currencyBase, 'BTC')))
        {
            return parent::_convert ($currencyFrom, $currencyTo, $retry);
        }

        $url = str_replace ('{{CURRENCY_FROM}}', $currencyFrom, $this->_blockchainUrl);

        try
        {
            $response = $this->_httpClient
                ->setUri ($url)
                ->setConfig (array ('timeout' => Mage::getStoreConfig ('currency/blockchain/timeout')))
                ->request ('GET')
                ->getRawBody ()
            ;

            if (empty ($response))
            {
                $this->_messages [] = Mage::helper ('directory')->__('Cannot retrieve rate from %s.', $url);

                return null;
            }

            // FIX: Error parsing body - doesn't seem to be a chunked message
            $decoded = gzdecode ($response);
            if (is_numeric ($decoded))
            {
                $response = $decoded;
            }

            return $response;
        }
        catch (Exception $e)
        {
            if ($retry == 0)
            {
                $this->_convert ($currencyFrom, $currencyTo, 1);
            }
            else
            {
                $this->_messages [] = Mage::helper ('directory')->__('Cannot retrieve rate from %s.', $url);
            }
        }
    }

    /**
     * Retrieve currency codes
     *
     * @return array
     */
    protected function _getCurrencyCodes ()
    {
        $currencyCodes = parent::_getCurrencyCodes ();

        return array_merge ($currencyCodes, array ('BTC'));
    }
}

