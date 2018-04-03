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

class Gamuza_Blockchain_Helper_Data extends Mage_Core_Helper_Abstract
{
    const SATOSHI = 100000000;

    const PAYMENT_IMAGE_PREFIX = 'images/gamuza/blockchain/';

    const API_REQUEST_CURRENCY_TO_BTC        = 'https://blockchain.info/tobtc?currency={{CURRENCY_FROM}}&value=1';
    const API_REQUEST_NOTIFICATION_NUMBER    = 6;
    const API_REQUEST_NOTIFICATION_BEHAVIOUR = 'DELETE';
    const API_REQUEST_OPERATION_TYPE         = 'RECEIVE';
    const API_REQUEST_BLOCK_CONFIRMATIONS    = 1;

    const API_RECEIVE_ADDRESS_METHOD            = 'receive';
    const API_RECEIVE_BALANCE_UPDATE_METHOD     = 'receive/balance_update';
    const API_RECEIVE_BLOCK_NOTIFICATION_METHOD = 'receive/block_notification';

    const ORDER_PAYMENT_ATTRIBUTE_BLOCKCHAIN_ADDRESS = 'blockchain_address';
    const ORDER_PAYMENT_ATTRIBUTE_BLOCKCHAIN_AMOUNT  = 'blockchain_amount';

    const XML_GLOBAL_PAYMENT_BLOCKCHAIN_TYPES = 'global/payment/blockchain/types';

    const XML_PAYMENT_BLOCKCHAIN_KEY           = 'payment/gamuza_blockchain_info/api_key';
    const XML_PAYMENT_BLOCKCHAIN_XPUB          = 'payment/gamuza_blockchain_info/xpub_key';
    const XML_PAYMENT_BLOCKCHAIN_GAP           = 'payment/gamuza_blockchain_info/gap_limit';
    const XML_PAYMENT_BLOCKCHAIN_CONFIRMATIONS = 'payment/gamuza_blockchain_info/confirmations';
    const XML_PAYMENT_BLOCKCHAIN_KEEP_RETRIES  = 'payment/gamuza_blockchain_info/keep_retries';
    const XML_PAYMENT_BLOCKCHAIN_ORDER_STATUS  = 'payment/gamuza_blockchain_info/order_status';
    const XML_PAYMENT_BLOCKCHAIN_USE_BLOCK     = 'payment/gamuza_blockchain_info/use_block';
    const XML_PAYMENT_BLOCKCHAIN_BLOCK_CONFS   = 'payment/gamuza_blockchain_info/block_confirmations';
    const XML_PAYMENT_BLOCKCHAIN_BLOCK_HEIGHT  = 'payment/gamuza_blockchain_info/block_height';

    const STATUS_CREATED = 'created';
    const STATUS_PENDING = 'pending';
    const STATUS_BALANCE = 'balance';
    const STATUS_BLOCK   = 'block';
    const STATUS_ADDED   = 'added';
    const STATUS_PAID    = 'paid';
    const STATUS_ERROR   = 'error';

    const LOG = 'blockchain.log';

    public function api ($method, $post = null, $request = null)
    {
        $url     = $this->getStoreConfig ('api_url');
        $timeout = $this->getStoreConfig ('timeout');

        $curl = curl_init ();

        curl_setopt ($curl, CURLOPT_URL, $url . $method);
        curl_setopt ($curl, CURLOPT_TIMEOUT, $timeout);
        curl_setopt ($curl, CURLOPT_HTTPHEADER, array ('Content-Type: application/json'));
        curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($curl, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt ($curl, CURLOPT_SSL_VERIFYHOST, 2);

        if ($post != null)
        {
            if (empty ($request)) $request = 'POST';

            curl_setopt ($curl, CURLOPT_POST, 1);
            curl_setopt ($curl, CURLOPT_POSTFIELDS, json_encode ($post));
        }

        if ($request != null)
        {
            curl_setopt ($curl, CURLOPT_CUSTOMREQUEST, $request);
        }

        $result   = curl_exec ($curl);
        $response = json_decode ($result);
        $info     = curl_getinfo ($curl);

        $message = null;
        switch ($httpCode = $info ['http_code'])
        {
            case 400: { $message = 'Invalid Request';      break; }
            case 401: { $message = 'Authentication Error'; break; }
            case 403: { $message = 'Permission Denied';    break; }
            case 404: { $message = 'Invalid URL';          break; }
            case 409: { $message = 'Resource Exists';      break; }
            case 500: { $message = 'Internal Error';       break; }
            case 200: { $message = null; /* Success! */    break; }
        }

        if (!empty ($message))
        {
            $message = implode (' : ' , array ($request, $method, json_encode ($post), $message, $result));

            throw Mage::exception ('Gamuza_Blockchain', $message, $httpCode);
        }

        curl_close ($curl);

        return $response;
    }

    public function convertCurrency ($amount, $from, $to)
    {
        return Mage::helper ('directory')->currencyConvert ($amount, $from, $to);
    }

    public function formatPrice ($currencyCode, $amount)
    {
        return Mage::app ()->getLocale ()->currency ($currencyCode)->toCurrency ($amount);
    }

    public function getCurrency ($currencyCode)
    {
        return Mage::getModel ('directory/currency')->load ($currencyCode);
    }

    public function getBaseCurrencyCode ()
    {
        return Mage::app ()->getBaseCurrencyCode ();
    }

    public function getBaseGrandTotal ()
    {
        return $this->getQuote ()->getBaseGrandTotal ();
    }

    public function getGrandTotal ()
    {
        return $this->getQuote ()->getGrandTotal ();
    }

    public function getCheckout ()
    {
        $sessionName = Mage::app ()->getStore ()->isAdmin () ? 'adminhtml/session_quote' : 'checkout/session';

        return Mage::getSingleton ($sessionName);
    }

    public function getQuote ()
    {
        return $this->getCheckout ()->getQuote ();
    }

    public function getConfig ()
    {
        return Mage::getModel ('blockchain/payment_config');
    }

    public function getStoreConfig ($key)
    {
        return Mage::getStoreConfig ("payment/gamuza_blockchain_info/{$key}");
    }
}

