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
 * Locale model
 */
class Gamuza_Blockchain_Model_Core_Locale extends Mage_Core_Model_Locale
{
    protected $_paymentTypes = array ();

    public function __construct ($locale = null)
    {
        parent::__construct ($locale);

        $this->_paymentTypes = Mage::helper ('blockchain')->getConfig ()->getCcTypes ();
    }

    public function currency ($currency)
    {
        if (!in_array ($currency, array_keys ($this->_paymentTypes)))
        {
            return parent::currency ($currency);
        }

        Varien_Profiler::start ('locale/currency');

        if (!isset (self::$_currencyCache [$this->getLocaleCode ()][$currency]))
        {
            $options = array (
                'currency'  => $currency,
                'display'   => Zend_Currency::USE_SYMBOL,
                'precision' => intval ($this->_paymentTypes [$currency]['precision']),
                'name'      => $this->_paymentTypes [$currency]['name'],
                'symbol'    => $this->_paymentTypes [$currency]['symbol'],
            );

            /*
            try
            {
                $currencyObject = new Zend_Currency ($currency, $this->getLocale ());
            }
            catch (Exception $e)
            */
            {
                /**
                 * catch specific exceptions like "Currency 'USD' not found"
                 * - back end falls with specific locals as Malaysia and etc.
                 *
                 * as we can see from Zend framework ticket
                 * http://framework.zend.com/issues/browse/ZF-10038
                 * zend team is not going to change it behaviour in the near time
                 */
                $currencyObject = new Zend_Currency ($options, $this->getLocaleCode ());
                /*
                $options ['name']     = $currency;
                $options ['currency'] = $currency;
                $options ['symbol']   = $currency;
                */
            }

            $options = new Varien_Object ($options);

            Mage::dispatchEvent ('currency_display_options_forming', array(
                'currency_options' => $options,
                'base_code' => $currency
            ));

            $currencyObject->setFormat ($options->toArray ());

            self::$_currencyCache [$this->getLocaleCode ()][$currency] = $currencyObject;
        }

        Varien_Profiler::stop ('locale/currency');

        return self::$_currencyCache [$this->getLocaleCode ()][$currency];
    }

    public function getTranslation ($value = null, $path = null)
    {
        if (in_array ($value, array_keys ($this->_paymentTypes)) && !strcmp ($path, 'nametocurrency'))
        {
            return $this->_paymentTypes [$value]['name'];
        }

        return parent::getTranslation ($value, $path);
    }

    public function getTranslationList ($path = null, $value = null)
    {
        $translationList = parent::getTranslationList ($path, $value);

        if (strcmp ($path, 'currencytoname')) return $translationList;

        $additionalList = array ();

        foreach ($this->_paymentTypes as $type)
        {
            $additionalList [$type ['name']] = $type ['code'];
        }

        return array_merge ($translationList, $additionalList);
    }
}

