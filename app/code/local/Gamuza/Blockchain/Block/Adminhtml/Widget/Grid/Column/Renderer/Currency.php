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
 * Adminhtml grid item renderer currency
 */
class Gamuza_Blockchain_Block_Adminhtml_Widget_Grid_Column_Renderer_Currency
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Currency
{
    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row)
    {
        if ($data = (string) $row->getData ($this->getColumn ()->getIndex ()))
        {
            $currency_code = $this->_getCurrencyCode ($row);

            if (!$currency_code) return $data;

            $data = floatval ($data) * $this->_getRate ($row);
            $sign = (bool)(int) $this->getColumn ()->getShowNumberSign () && ($data > 0) ? '+' : '';
            // $data = sprintf ("%F", $data);
            $data = Mage::app ()->getLocale ()->currency ($currency_code)->toCurrency ($data);

            return $sign . $data;
        }

        return $this->getColumn ()->getDefault ();
    }
}

