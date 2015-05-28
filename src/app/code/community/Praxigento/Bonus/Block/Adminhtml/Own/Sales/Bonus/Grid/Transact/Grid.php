<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;
use Praxigento_Bonus_Model_Own_Order as Order;
use Praxigento_Bonus_Model_Own_Payout_Transact as PayoutTransact;
use Praxigento_Bonus_Model_Own_Transact as Transact;

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Block_Adminhtml_Own_Sales_Bonus_Grid_Transact_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    const AS_BONUS_CUST_ID = 'customer_inc';
    const AS_BONUS_CUST_NAME = 'bonus_cust_name';
    const AS_CUST_NAME = 'customer_name';
    const AS_ORDER_CUST_ID = 'order_cust_mlm_id';
    const AS_ORDER_CUST_NAME = 'order_cust_name';
    const AS_ORDER_INC_ID = 'order_inc_id';
    const AS_PAYOUT = 'payout';
    const AS_TMP_EMAIL = 'tmp_email';

    public function __construct()
    {
        parent::__construct();
        $this->setId('prxgt_bonus_grid_transact');
        $this->setDefaultSort(Transact::ATTR_ID);
        $this->setDefaultDir('DESC');
//        $this->setSaveParametersInSession(true);
    }

    /*
    SELECT
      `main_table`.*,
      `cust`.`nmmlm_core_mlm_id` AS `customer_inc`,
      `sord`.`increment_id` AS `order_inc_id`,
      CONCAT(sord.customer_firstname, ' ', sord.customer_lastname, ' <', LOWER(sord.customer_email), '>') AS `order_cust_name`,
      `ocust`.`nmmlm_core_mlm_id` AS `order_cust_mlm_id`,
      `pt`.`payout_id` AS `payout`,
      CONCAT(bfirst.`value`, ' ', blast.`value`, ' <', cust.email, '>') AS `bonus_cust_name`
    FROM `prxgt_bonus_transact` AS `main_table`
      INNER JOIN `customer_entity` AS `cust`
        ON main_table.customer_id = cust.entity_id
      INNER JOIN `prxgt_bonus_order` AS `bord`
        ON main_table.id = bord.transact_id
      LEFT JOIN `sales_flat_order` AS `sord`
        ON bord.order_id = sord.entity_id
      LEFT JOIN `customer_entity` AS `ocust`
        ON sord.customer_id = ocust.entity_id
      LEFT JOIN `prxgt_bonus_payout_transact` AS `pt`
        ON main_table.id = pt.transact_id
      LEFT JOIN `eav_attribute` AS `bfirsta`
        ON bfirsta.entity_type_id = 1
        AND bfirsta.attribute_code = 'firstname'
      LEFT JOIN `customer_entity_varchar` AS `bfirst`
        ON bfirst.attribute_id = bfirsta.attribute_id
        AND bfirst.entity_id = main_table.customer_id
      LEFT JOIN `eav_attribute` AS `blasta`
        ON blasta.entity_type_id = 1
        AND blasta.attribute_code = 'lastname'
      LEFT JOIN `customer_entity_varchar` AS `blast`
        ON blast.attribute_id = blasta.attribute_id
        AND blast.entity_id = main_table.customer_id
     */
    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        /** @var  $collection Praxigento_Bonus_Resource_Own_Transact_Collection */
        $collection = Mage::getResourceModel(Config::CFG_MODEL . '/own_transact_collection');
        $rsrc = $collection->getResource();
        $conn = $rsrc->getReadConnection();
        /* JOIN customer_entity */
        $tbl = array('cust' => 'customer/entity');
        $cond = 'main_table.' . Transact::ATTR_CUSTOMER_ID . '=cust.entity_id';
        $cols = array(
            self::AS_BONUS_CUST_ID => Nmmlm_Core_Config::ATTR_CUST_MLM_ID
        );
        $collection->join($tbl, $cond, $cols);
        /* JOIN prxgt_bonus_order */
        $tbl = array('bord' => Config::CFG_MODEL . '/' . Config::CFG_ENTITY_ORDER);
        $cond = 'main_table.' . Transact::ATTR_ID . '=bord.' . Order::ATTR_TRANSACT_ID;
        $cols = array();
        $collection->join($tbl, $cond, $cols);
        /* JOIN sales_flat_order */
        $tbl = array('sord' => $rsrc->getTable('sales/order'));
        $cond = 'bord.' . Order::ATTR_ORDER_ID . '=sord.entity_id';
        $cols = array(
            self::AS_ORDER_INC_ID => 'increment_id',
            self::AS_ORDER_CUST_NAME =>
                'CONCAT(sord.customer_firstname, \' \', sord.customer_lastname, \' <\', LOWER(sord.customer_email), \'>\')'
        );
        $collection->getSelect()->joinLeft($tbl, $cond, $cols);
        /* JOIN customer_entity as order customer*/
        $tbl = array('ocust' => $rsrc->getTable('customer/entity'));
        $cond = 'sord.customer_id=ocust.entity_id';
        $cols = array(
            self::AS_ORDER_CUST_ID => Nmmlm_Core_Config::ATTR_CUST_MLM_ID
        );
        $collection->getSelect()->joinLeft($tbl, $cond, $cols);
        /* JOIN payout_transact */
        $tbl = array('pt' => $rsrc->getTable(Config::CFG_MODEL . '/' . Config::CFG_ENTITY_PAYOUT_TRANSACT));
        $cond = 'main_table.' . Transact::ATTR_ID . '=pt.' . PayoutTransact::ATTR_TRANSACT_ID;
        $cols = array(self::AS_PAYOUT => PayoutTransact::ATTR_PAYOUT_ID);
        $collection->getSelect()->joinLeft($tbl, $cond, $cols);
        /**
         * JOIN bonus customer first and last names
         */
        /* JOIN first name */
        $tbl = array('bfirsta' => $rsrc->getTable('eav/attribute'));
        $cond = 'bfirsta.entity_type_id=1 AND bfirsta.attribute_code=\'firstname\'';
        $cols = array();
        $collection->getSelect()->joinLeft($tbl, $cond, $cols);
        $tbl = array('bfirst' => $conn->getTableName('customer_entity_varchar'));
        $cond = 'bfirst.attribute_id=bfirsta.attribute_id AND bfirst.entity_id=main_table.' . Transact::ATTR_CUSTOMER_ID;
        $cols = array();
        $collection->getSelect()->joinLeft($tbl, $cond, $cols);
        /* JOIN last name */
        $tbl = array('blasta' => $rsrc->getTable('eav/attribute'));
        $cond = 'blasta.entity_type_id=1 AND blasta.attribute_code=\'lastname\'';
        $cols = array();
        $collection->getSelect()->joinLeft($tbl, $cond, $cols);
        $tbl = array('blast' => $conn->getTableName('customer_entity_varchar'));
        $cond = 'blast.attribute_id=blasta.attribute_id AND blast.entity_id=main_table.' . Transact::ATTR_CUSTOMER_ID;
        $cols = array(
            self::AS_BONUS_CUST_NAME =>
                "CONCAT(bfirst.`value`, ' ', blast.`value`, ' <', cust.email,'>')");
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

        $this->addColumn(self::AS_ORDER_INC_ID, array(
            'header' => $helper->__('Order #'),
            'filter' => false,
            'index' => self::AS_ORDER_INC_ID
        ));

        $this->addColumn(self::AS_ORDER_CUST_NAME, array(
            'header' => $helper->__('Order Customer'),
            'filter' => false,
            'index' => self::AS_ORDER_CUST_NAME
        ));

        $this->addColumn(self::AS_ORDER_CUST_ID, array(
            'header' => $helper->__('Order Customer #'),
            'filter' => false,
            'index' => self::AS_ORDER_CUST_ID
        ));

        $this->addColumn(self::AS_BONUS_CUST_ID, array(
            'header' => $helper->__('Bonus Customer #'),
            'filter' => false,
            'index' => self::AS_BONUS_CUST_ID
        ));

        $this->addColumn(self::AS_BONUS_CUST_NAME, array(
            'header' => $helper->__('Bonus Customer'),
            'filter' => false,
            'index' => self::AS_BONUS_CUST_NAME
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