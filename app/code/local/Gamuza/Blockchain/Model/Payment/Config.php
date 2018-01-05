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
 * Payment configuration model
 *
 * Used for retrieving configuration data by payment models
 */
class Gamuza_Blockchain_Model_Payment_Config extends Mage_Payment_Model_Config
{
    /**
     * Retrieve array of credit card types
     *
     * @return array
     */
    public function getCcTypes ()
    {
        $_types = Mage::getConfig ()->getNode (Gamuza_Blockchain_Helper_Data::XML_GLOBAL_PAYMENT_BLOCKCHAIN_TYPES)->asArray ();

        uasort ($_types, array ('Gamuza_Blockchain_Model_Payment_Config', 'compareCcTypes'));
/*
        $types = array ();
        foreach ($_types as $data)
        {
            if (isset ($data ['code']) && isset ($data ['name']))
            {
                $types [$data ['code']] = $data['name'];
            }
        }
*/
        return $_types;
    }
}

