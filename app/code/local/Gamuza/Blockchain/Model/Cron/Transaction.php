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

class Gamuza_Blockchain_Model_Cron_Transaction extends Gamuza_Blockchain_Model_Cron_Abstract
{
    protected $_confirmationsNumber = 0;

    public function _construct ()
    {
        parent::_construct ();

        $this->_confirmationsNumber = intval (Mage::getStoreConfig (Gamuza_Blockchain_Helper_Data::XML_PAYMENT_BLOCKCHAIN_CONFIRMATIONS));
        if (empty ($this->_confirmationsNumber)) $this->_confirmationsNumber = Gamuza_Blockchain_Helper_Data::API_REQUEST_NOTIFICATION_NUMBER;
    }

    private function readBlockchainTransactionsCollection ()
    {
        $collection = Mage::getModel ('blockchain/transaction')->getCollection ();

        $subCollection = Mage::getModel ('blockchain/transaction')->getCollection ()
            ->addFieldToFilter ('address',     array ('neq' => ""))
            ->addFieldToFilter ('external_id', array ('null' => true))
        ;

        $subCollection->getSelect ()->order ('entity_id DESC');

        $collection->getSelect ()->reset (Zend_Db_Select::FROM)
            ->from ($subCollection->getSelect ())
            ->group ('order_id')
            ->reset (Zend_Db_Select::COLUMNS)
            ->columns ('t.*')
        ;

        return $collection;
    }

    private function updateBlockchainTransactions ($collection)
    {
        foreach ($collection as $transaction)
        {
            $externalId = null;

            try
            {
                $externalId = $this->updateBlockchainTransaction ($transaction);
            }
            catch (Exception $e)
            {
                $this->logBlockchainTransaction ($transaction, $e->getMessage ());

                Mage::logException ($e);
            }

            if (!empty ($externalId)) $this->cleanupBlockchainTransaction ($transaction, $externalId);
        }

        return true;
    }

    private function updateBlockchainTransaction (Gamuza_Blockchain_Model_Transaction $transaction)
    {
        $postBalance = array(
            'addr'           => $transaction->getAddress (),
            'callback'       => $transaction->getCallback (),
            'key'            => $this->_apiKey,
            'confs'          => $this->_confirmationsNumber,
            'onNotification' => Gamuza_Blockchain_Helper_Data::API_REQUEST_NOTIFICATION_BEHAVIOUR,
            'op'             => Gamuza_Blockchain_Helper_Data::API_REQUEST_OPERATION_TYPE,
        );

        $externalId = true;

        try
        {
            $result = $this->getHelper ()->api (Gamuza_Blockchain_Helper_Data::API_RECEIVE_BALANCE_UPDATE_METHOD, $postBalance);

            $externalId = $result->id;
        }
        catch (Exception $e)
        {
            if ($e->getCode () != 409 /* Resource Exists */)
            {
                throw Mage::exception ('Gamuza_Blockchain', $e->getMessage (), $e->getCode ());
            }
        }

        return $externalId;
    }

    private function cleanupBlockchainTransaction (Gamuza_Blockchain_Model_Transaction $transaction, $externalId = null)
    {
        if ($externalId !== null && $externalId !== true)
        {
            $transaction->setExternalId ($externalId);
        }

        $transaction->setUpdatedAt (date ('c'))
            ->setStatus (Gamuza_Blockchain_Helper_Data::STATUS_BALANCE)
            ->setMessage (new Zend_Db_Expr ('NULL'))
            ->setConfirmations ($this->_confirmationsNumber)
            ->save ()
        ;

        return true;
    }

    private function logBlockchainTransaction (Gamuza_Blockchain_Model_Transaction $transaction, $message = null)
    {
        $transaction->setStatus (Gamuza_Blockchain_Helper_Data::STATUS_ERROR)->setMessage ($message)->save ();
    }

    public function run ()
    {
        // if (!$this->getStoreConfig ('active')) return false;

        $collection = $this->readBlockchainTransactionsCollection ();
        if (!$collection->count ()) return false;

        $this->updateBlockchainTransactions ($collection);

        return true;
    }
}

