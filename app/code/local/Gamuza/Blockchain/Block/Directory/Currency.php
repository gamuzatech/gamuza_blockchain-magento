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
 * Currency dropdown block
 */
class Gamuza_Blockchain_Block_Directory_Currency extends Mage_Directory_Block_Currency
{
    protected $_paymentTypes = array ();

    public function _construct ()
    {
        parent::_construct ();

        $this->_paymentTypes = Mage::helper ('blockchain')->getConfig ()->getCcTypes ();
    }

    public function getCurrencies ()
    {
        $currencies = parent::getCurrencies ();

        if (!Mage::getStoreConfigFlag ('currency/options/crypto'))
        {
            foreach ($this->_paymentTypes as $code => $type)
            {
                unset ($currencies [$code]);
            }
        }

        return $currencies;
    }
}

