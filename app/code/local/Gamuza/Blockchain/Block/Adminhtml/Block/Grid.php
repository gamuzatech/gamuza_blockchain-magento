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

class Gamuza_Blockchain_Block_Adminhtml_Block_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct ()
	{
		parent::__construct ();

		$this->setId ('blockchainBlockGrid');
		$this->setDefaultSort ('entity_id');
		$this->setDefaultDir ('DESC');
		$this->setSaveParametersInSession (true);
	}

	protected function _prepareCollection ()
	{
		$collection = Mage::getModel ('blockchain/block')->getCollection ();
        $collection->getSelect ()->join (
            array ('sfo' => Mage::getSingleton ('core/resource')->getTableName ('sales/order')),
            'main_table.order_id = sfo.entity_id',
            array ('order_increment_id' => 'sfo.increment_id', 'base_grand_total', 'base_currency_code')
        );
		$this->setCollection ($collection);

		return parent::_prepareCollection ();
	}

	protected function _prepareColumns ()
	{
		$this->addColumn ('entity_id', array(
		    'header' => Mage::helper ('blockchain')->__('ID'),
		    'align'  => 'right',
		    'width'  => '50px',
	        'type'   => 'number',
		    'index'  => 'entity_id',
		));
/*
		$this->addColumn ('customer_id', array(
		    'header' => Mage::helper ('blockchain')->__('Customer ID'),
		    'align'  => 'right',
		    'width'  => '50px',
	        'type'   => 'number',
		    'index'  => 'customer_id',
		));
		$this->addColumn ('order_id', array(
		    'header' => Mage::helper ('blockchain')->__('Order ID'),
		    'align'  => 'right',
		    'width'  => '50px',
	        'type'   => 'number',
		    'index'  => 'order_id',
		));
*/
		$this->addColumn ('order_increment_id', array(
		    'header' => Mage::helper ('blockchain')->__('Order Inc. ID'),
		    'align'  => 'center',
		    'index'  => 'order_increment_id',
		));
		$this->addColumn ('base_grand_total', array(
		    'header'   => Mage::helper ('blockchain')->__('G.T. (Base)'),
		    'index'    => 'base_grand_total',
            'type'     => 'currency',
            'currency' => 'base_currency_code',
		));
		$this->addColumn ('external_id', array(
		    'header' => Mage::helper ('blockchain')->__('Ext. ID'),
		    'align'  => 'center',
		    'index'  => 'external_id',
		));
		$this->addColumn ('callback', array(
		    'header' => Mage::helper ('blockchain')->__('Callback'),
		    'index'  => 'callback',
		));
		$this->addColumn ('confirmations', array(
		    'header' => Mage::helper ('blockchain')->__('Confirmations'),
		    'width'  => '50px',
	        'type'   => 'number',
		    'index'  => 'confirmations',
		));
		$this->addColumn ('height', array(
		    'header' => Mage::helper ('blockchain')->__('Height'),
		    'index'  => 'height',
            'type'   => 'number',
		));
		$this->addColumn ('hash', array(
		    'header' => Mage::helper ('blockchain')->__('Hash'),
		    'index'  => 'hash',
		));
		$this->addColumn ('timestamp', array(
		    'header' => Mage::helper ('blockchain')->__('Timestamp'),
		    'index'  => 'timestamp',
		));
		$this->addColumn ('size', array(
		    'header' => Mage::helper ('blockchain')->__('Size'),
		    'index'  => 'size',
		));
		$this->addColumn ('status', array(
		    'header'  => Mage::helper ('blockchain')->__('Status'),
		    'index'   => 'status',
            'type'    => 'options',
            'options' => Mage::getModel ('blockchain/adminhtml_system_config_source_status')->toArray (),
		));
		$this->addColumn ('message', array(
		    'header' => Mage::helper ('blockchain')->__('Message'),
		    'index'  => 'message',
		));
		$this->addColumn ('created_at', array(
		    'header' => Mage::helper ('blockchain')->__('Created At'),
		    'type'   => 'datetime',
		    'index'  => 'created_at',
		));
		$this->addColumn ('updated_at', array(
		    'header' => Mage::helper ('blockchain')->__('Updated At'),
	        'type'   => 'datetime',
		    'index'  => 'updated_at',
		));
		$this->addColumn ('synced_at', array(
		    'header' => Mage::helper ('blockchain')->__('Synced At'),
	        'type'   => 'datetime',
		    'index'  => 'synced_at',
		));

        $this->addColumn ('action',
            array(
                'header'   => Mage::helper ('blockchain')->__('Order'),
                'width'    => '50px',
                'type'     => 'action',
                'getter'   => 'getOrderId',
                'filter'   => false,
                'sortable' => false,
                'index'    => 'stores',
                'actions'  => array(
                    array(
                        'field'   => 'order_id',
                        'caption' => Mage::helper ('blockchain')->__('View'),
                        'url'     => array(
                            'base'   => 'adminhtml/sales_order/view',
                            'params' => array ('store' => $this->getRequest ()->getParam ('store'))
                        ),
                    )
                ),
        ));

		return parent::_prepareColumns ();
	}

	public function getRowUrl ($row)
	{
	    // nothing here
	}
}

