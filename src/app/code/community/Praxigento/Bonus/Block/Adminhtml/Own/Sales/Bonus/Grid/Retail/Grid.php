<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;
use Praxigento_Bonus_Model_Own_Order as Order;

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Block_Adminhtml_Own_Sales_Bonus_Grid_Retail_Grid
    extends Mage_Adminhtml_Block_Widget_Grid {
    const AS_CUST_ID = 'customer_inc';
    const AS_ORDER_ID = 'order_inc';

    public function __construct() {
        parent::__construct();
        $this->setId('prxgt_bonus_grid_retail');
        $this->setDefaultSort(Order::ATTR_ID);
        $this->setDefaultDir('DESC');
        //        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {
        /** @var  $collection Praxigento_Bonus_Resource_Own_Order_Collection */
        $collection = Mage::getResourceModel(Config::CFG_MODEL . '/own_order_collection');
        /* JOIN sales_order */
        $tbl = array( 'ord' => 'sales/order' );
        $cond = 'main_table.' . Order::ATTR_ORDER_ID . '=ord.entity_id';
        $cols = array( self::AS_ORDER_ID => 'increment_id' );
        $collection->join($tbl, $cond, $cols);
        /* JOIN customer_entity */
        $tbl = array( 'cust' => 'customer/entity' );
        $cond = 'main_table.' . Order::ATTR_UPLINE_ID . '=cust.entity_id';
        $cols = array( self::AS_CUST_ID => Nmmlm_Core_Config::ATTR_CUST_MLM_ID );
        $collection->join($tbl, $cond, $cols);
        /* prepare collection */
        $sql = $collection->getSelectSql(true);
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns() {
        $helper = Mage::helper(Config::CFG_HELPER);
        $currency = (string)Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE);

        $this->addColumn(Order::ATTR_ID, array(
            'header' => $helper->__('#'),
            'filter' => false,
            'index'  => Order::ATTR_ID
        ));

        $this->addColumn(self::AS_ORDER_ID, array(
            'header' => $helper->__('Order #'),
            'filter' => false,
            'index'  => self::AS_ORDER_ID
        ));

        $this->addColumn(self::AS_CUST_ID, array(
            'header' => $helper->__('Customer #'),
            'filter' => false,
            'index'  => self::AS_CUST_ID
        ));

        $this->addColumn(Order::ATTR_AMOUNT, array(
            'header'        => $helper->__('Bonus Amount'),
            'index'         => Order::ATTR_AMOUNT,
            'type'          => 'currency',
            'filter'        => false,
            'currency_code' => $currency
        ));

        $this->addColumn(Order::ATTR_FEE, array(
            'header'        => $helper->__('Fee Amount'),
            'index'         => Order::ATTR_FEE,
            'type'          => 'currency',
            'filter'        => false,
            'currency_code' => $currency
        ));

        $this->addColumn(Order::ATTR_TRANSACT_ID, array(
            'header' => $helper->__('Transaction #'),
            'filter' => false,
            'index'  => Order::ATTR_TRANSACT_ID
        ));
        return parent::_prepareColumns();
    }

}