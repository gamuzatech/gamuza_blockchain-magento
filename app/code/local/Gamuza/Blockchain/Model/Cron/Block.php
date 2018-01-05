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

class Gamuza_Blockchain_Model_Cron_Block extends Gamuza_Blockchain_Model_Cron_Abstract
{
    private function readBlockchainBlocksCollection ()
    {
        $collection = Mage::getModel ('blockchain/block')->getCollection ()
            ->addFieldToFilter ('external_id', array ('null'  => true))
        ;

        return $collection;
    }

    private function updateBlockchainBlocks ($collection)
    {
        foreach ($collection as $block)
        {
            $externalId = null;

            try
            {
                $externalId = $this->updateBlockchainBlock ($block);
            }
            catch (Exception $e)
            {
                $this->logBlockchainBlock ($block, $e->getMessage ());

                Mage::logException ($e);
            }

            if (!empty ($externalId)) $this->cleanupBlockchainBlock ($block, $externalId);
        }

        return true;
    }

    private function updateBlockchainBlock (Gamuza_Blockchain_Model_Block $block)
    {
        $postBlock = array(
            'key'            => $this->_apiKey,
            'callback'       => $block->getCallback (),
            'confs'          => $block->getConfirmations (),
            'onNotification' => Gamuza_Blockchain_Helper_Data::API_REQUEST_NOTIFICATION_BEHAVIOUR,
        );

        $externalId = true;

        try
        {
            $result = $this->getHelper ()->api (Gamuza_Blockchain_Helper_Data::API_RECEIVE_BLOCK_NOTIFICATION_METHOD, $postBlock);

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

    private function cleanupBlockchainBlock (Gamuza_Blockchain_Model_Block $block, $externalId = null)
    {
        if ($externalId !== null && $externalId !== true)
        {
            $block->setExternalId ($externalId);
        }

        $block->setUpdatedAt (date ('c'))
            ->setMessage (new Zend_Db_Expr ('NULL'))
            ->setStatus (Gamuza_Blockchain_Helper_Data::STATUS_BLOCK)
            ->save ()
        ;

        return true;
    }

    private function logBlockchainBlock (Gamuza_Blockchain_Model_Block $block, $message = null)
    {
        $block->setStatus (Gamuza_Blockchain_Helper_Data::STATUS_ERROR)->setMessage ($message)->save ();
    }

    public function run ()
    {
        // if (!$this->getStoreConfig ('active')) return false;

        $collection = $this->readBlockchainBlocksCollection ();
        if (!$collection->count ()) return false;

        $this->updateBlockchainBlocks ($collection);

        return true;
    }
}

