<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;
use Praxigento_Bonus_Model_Own_Payout_Transact as PayoutTransact;
use Praxigento_Bonus_Model_Own_Transact as Transact;

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Block_Adminhtml_Own_Sales_Bonus_Grid_Transact_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    const AS_CUST_ID = 'customer_inc';
    const AS_PAYOUT = 'payout';

    public function __construct()
    {
        parent::__construct();
        $this->setId('prxgt_bonus_grid_transact');
        $this->setDefaultSort(Transact::ATTR_ID);
        $this->setDefaultDir('DESC');
//        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        /** @var  $collection Praxigento_Bonus_Resource_Own_Transact_Collection */
        $collection = Mage::getResourceModel(Config::CFG_MODEL . '/own_transact_collection');
        $rsrc = $collection->getResource();
        /* JOIN customer_entity */
        $tbl = array('cust' => 'customer/entity');
        $cond = 'main_table.' . Transact::ATTR_CUSTOMER_ID . '=cust.entity_id';
        $cols = array(self::AS_CUST_ID => Nmmlm_Core_Config::ATTR_CUST_MLM_ID);
        $collection->join($tbl, $cond, $cols);
        /* JOIN payout_transact */
        $tbl = array('pt' => $rsrc->getTable(Config::CFG_MODEL . '/' . Config::CFG_ENTITY_PAYOUT_TRANSACT));
        $cond = 'main_table.' . Transact::ATTR_ID . '=pt.' . PayoutTransact::ATTR_TRANSACT_ID;
        $cols = array(self::AS_PAYOUT => PayoutTransact::ATTR_PAYOUT_ID);
        $collection->getSelect()->joinLeft($tbl, $cond, $cols);
        /* prepare collection */
        $sql = $collection->getSelectSql(true);
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns()
    {
        $helper = Mage::helper(Config::CFG_HELPER);
        $currency = (string)Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE);

        $this->addColumn(Transact::ATTR_ID, array(
            'header' => $helper->__('#'),
            'filter' => false,
            'index' => Transact::ATTR_ID
        ));

        $this->addColumn(Transact::ATTR_DATE_CREATED, array(
            'header' => $helper->__('Created at'),
            'filter' => false,
            'index' => Transact::ATTR_DATE_CREATED,
            'type' => 'datetime'
        ));

        $this->addColumn(self::AS_CUST_ID, array(
            'header' => $helper->__('Customer #'),
            'filter' => false,
            'index' => self::AS_CUST_ID
        ));

        $this->addColumn(Transact::ATTR_AMOUNT, array(
            'header' => $helper->__('Amount'),
            'index' => Transact::ATTR_AMOUNT,
            'type' => 'currency',
            'filter' => false,
            'currency_code' => $currency
        ));

        $this->addColumn(self::AS_PAYOUT, array(
            'header' => $helper->__('Payout #'),
            'filter' => false,
            'index' => self::AS_PAYOUT
        ));

        return parent::_prepareColumns();
    }

}