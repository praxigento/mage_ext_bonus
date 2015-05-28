<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;
use Praxigento_Bonus_Model_Own_Payout as Payout;

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Block_Adminhtml_Own_Sales_Bonus_Grid_Payout_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    const AS_CUST_ID = 'customer_inc';

    public function __construct()
    {
        parent::__construct();
        $this->setId('prxgt_bonus_grid_payout');
        $this->setDefaultSort(Payout::ATTR_ID);
        $this->setDefaultDir('DESC');
//        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        /** @var  $collection Praxigento_Bonus_Resource_Own_Payout_Collection */
        $collection = Mage::getResourceModel(Config::CFG_MODEL . '/own_payout_collection');
        /* JOIN customer_entity */
        $tbl = array('cust' => 'customer/entity');
        $cond = 'main_table.' . Payout::ATTR_CUSTOMER_ID . '=cust.entity_id';
        $cols = array(self::AS_CUST_ID => Nmmlm_Core_Config::ATTR_CUST_MLM_ID);
        $collection->join($tbl, $cond, $cols);
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

        $this->addColumn(Payout::ATTR_ID, array(
            'header' => $helper->__('#'),
            'filter' => false,
            'index' => Payout::ATTR_ID
        ));

        $this->addColumn(Payout::ATTR_DATE_CREATED, array(
            'header' => $helper->__('Created at'),
            'filter' => false,
            'index' => Payout::ATTR_DATE_CREATED,
            'type' => 'datetime'
        ));

        $this->addColumn(self::AS_CUST_ID, array(
            'header' => $helper->__('Customer #'),
            'filter' => false,
            'index' => self::AS_CUST_ID
        ));

        $this->addColumn(Payout::ATTR_AMOUNT, array(
            'header' => $helper->__('Amount'),
            'index' => Payout::ATTR_AMOUNT,
            'type' => 'currency',
            'filter' => false,
            'currency_code' => $currency
        ));

        $this->addColumn(Payout::ATTR_DESC, array(
            'header' => $helper->__('Description'),
            'filter' => false,
            'index' => Payout::ATTR_DESC
        ));

        $this->addColumn(Payout::ATTR_REFERENCE, array(
            'header' => $helper->__('eWallet Ref.'),
            'filter' => false,
            'index' => Payout::ATTR_REFERENCE
        ));

        $this->addColumn(Payout::ATTR_DATE_PAID, array(
            'header' => $helper->__('Paid at'),
            'filter' => false,
            'index' => Payout::ATTR_DATE_PAID,
            'type' => 'datetime'
        ));

        return parent::_prepareColumns();
    }

}