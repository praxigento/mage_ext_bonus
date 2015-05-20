<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;
use Praxigento_Bonus_Model_Own_Order as Order;
use Praxigento_Bonus_Model_Own_Payout as Payout;
use Praxigento_Bonus_Model_Own_Payout_Transact as PayoutTransact;
use Praxigento_Bonus_Model_Own_Transact as Transact;
use Varien_Db_Adapter_Interface as Db;
use Varien_Db_Ddl_Table as Ddl;

/**
 * User: Alex Gusev <alex@flancer64.com>
 */

/** include function to replace old columns with new ones */
include_once('prxgt_install_func.php');

/** @var $this Mage_Core_Model_Resource_Setup */
$this->startSetup();

/** @var $coreSetup Mage_Eav_Model_Entity_Setup */
$coreSetup = new Mage_Eav_Model_Entity_Setup('core_setup');
/** @var $conn Varien_Db_Adapter_Interface */
$conn = $this->getConnection();

/**
 * Table names.
 */
$tblOrder = $this->getTable(Config::CFG_MODEL . '/' . Config::CFG_ENTITY_ORDER);
$tblPayout = $this->getTable(Config::CFG_MODEL . '/' . Config::CFG_ENTITY_PAYOUT);
$tblPayoutTransact = $this->getTable(Config::CFG_MODEL . '/' . Config::CFG_ENTITY_PAYOUT_TRANSACT);
$tblTransact = $this->getTable(Config::CFG_MODEL . '/' . Config::CFG_ENTITY_TRANSACT);

$tblSalesOrder = $this->getTable('sales/order');
$tblCustomer = $this->getTable('customer/entity');

/** =================================================================================================================
 * Create tables.
 * =============================================================================================================== */
$optId = array('identity' => true, 'primary' => true, 'nullable' => false, 'unsigned' => true);

/**
 * Bonus Order
 */
$tbl = $conn->newTable($tblOrder);
$tbl->addColumn(Order::ATTR_ID, Ddl::TYPE_INTEGER, null, $optId,
    'Entity ID.');
$tbl->addColumn(Order::ATTR_ORDER_ID, Ddl::TYPE_INTEGER, null, array('nullable' => false, 'unsigned' => true),
    'Related order that generates bonus.');
$tbl->addColumn(Order::ATTR_UPLINE_ID, Ddl::TYPE_INTEGER, null, array('nullable' => false, 'unsigned' => true),
    'Customer that earns this bonus.');
$tbl->addColumn(Order::ATTR_AMOUNT, Ddl::TYPE_DECIMAL, '12,4', array('nullable' => false),
    'Bonus amount.');
$tbl->addColumn(Order::ATTR_CURR, Ddl::TYPE_CHAR, '3', array('nullable' => false),
    'Bonus amount currency.');
$tbl->addColumn(Order::ATTR_IS_CHARGED, Ddl::TYPE_BOOLEAN, null, array('nullable' => false, 'default' => false),
    'Is this bonus collected in payout.');
$tbl->setComment('Retail bonus amount for orders');
$conn->createTable($tbl);

/* Sales Order FK */
$fkName = $conn->getForeignKeyName(
    $tblOrder,
    Order::ATTR_ORDER_ID,
    $tblSalesOrder,
    Mage_Eav_Model_Entity::DEFAULT_ENTITY_ID_FIELD
);
$conn->addForeignKey(
    $fkName,
    $tblOrder,
    Order::ATTR_ORDER_ID,
    $tblSalesOrder,
    Mage_Eav_Model_Entity::DEFAULT_ENTITY_ID_FIELD,
    Db::FK_ACTION_RESTRICT,
    DB::FK_ACTION_RESTRICT
);

/* Upline Customer FK */
$fkName = $conn->getForeignKeyName(
    $tblOrder,
    Order::ATTR_UPLINE_ID,
    $tblCustomer,
    Mage_Eav_Model_Entity::DEFAULT_ENTITY_ID_FIELD
);
$conn->addForeignKey(
    $fkName,
    $tblOrder,
    Order::ATTR_UPLINE_ID,
    $tblCustomer,
    Mage_Eav_Model_Entity::DEFAULT_ENTITY_ID_FIELD,
    Db::FK_ACTION_RESTRICT,
    DB::FK_ACTION_RESTRICT
);

/* UQ index (customer_id:reference) */
$ndxFields = array(Order::ATTR_ORDER_ID);
$ndxName = $conn->getIndexName($tblOrder, $ndxFields, Db::INDEX_TYPE_UNIQUE);
$conn->addIndex($tblOrder, $ndxName, $ndxFields, Db::INDEX_TYPE_UNIQUE);


/**
 * Bonus Payout
 */
$tbl = $conn->newTable($tblPayout);
$tbl->addColumn(Payout::ATTR_ID, Ddl::TYPE_INTEGER, null, $optId,
    'Entity ID.');
$tbl->addColumn(Payout::ATTR_CUSTOMER_ID, Ddl::TYPE_INTEGER, null, array('nullable' => false, 'unsigned' => true),
    'Customer that gets this payout.');
$tbl->addColumn(Payout::ATTR_DATE_CREATED, Ddl::TYPE_TIMESTAMP, null, array('nullable' => false),
    'Payout creation date.');
$tbl->addColumn(Payout::ATTR_AMOUNT, Ddl::TYPE_DECIMAL, '12,4', array('nullable' => false),
    'Payout amount.');
$tbl->addColumn(Payout::ATTR_CURR, Ddl::TYPE_CHAR, '3', array('nullable' => false),
    'Payout currency.');
$tbl->addColumn(Payout::ATTR_REFERENCE, Ddl::TYPE_CHAR, 255, array('nullable' => false),
    'Reverence for correlated external payment (in IPS, ...).');
$tbl->setComment('Customer payouts (external payments).');
$conn->createTable($tbl);

/* Customer FK */
$fkName = $conn->getForeignKeyName(
    $tblPayout,
    Payout::ATTR_CUSTOMER_ID,
    $tblCustomer,
    Mage_Eav_Model_Entity::DEFAULT_ENTITY_ID_FIELD
);
$conn->addForeignKey(
    $fkName,
    $tblPayout,
    Payout::ATTR_CUSTOMER_ID,
    $tblCustomer,
    Mage_Eav_Model_Entity::DEFAULT_ENTITY_ID_FIELD,
    Db::FK_ACTION_RESTRICT,
    DB::FK_ACTION_RESTRICT
);

/* UQ index (customer_id:reference) */
$ndxFields = array(Payout::ATTR_CUSTOMER_ID, Payout::ATTR_REFERENCE);
$ndxName = $conn->getIndexName($tblPayout, $ndxFields, Db::INDEX_TYPE_UNIQUE);
$conn->addIndex($tblPayout, $ndxName, $ndxFields, Db::INDEX_TYPE_UNIQUE);


/**
 * Bonus Transaction
 */
$tbl = $conn->newTable($tblTransact);
$tbl->addColumn(Transact::ATTR_ID, Ddl::TYPE_INTEGER, null, $optId,
    'Entity ID.');
$tbl->addColumn(Transact::ATTR_CUSTOMER_ID, Ddl::TYPE_INTEGER, null, array('nullable' => false, 'unsigned' => true),
    'Customer that gets this payout.');
$tbl->addColumn(Transact::ATTR_DATE_CREATED, Ddl::TYPE_TIMESTAMP, null, array('nullable' => false),
    'Payout creation date.');
$tbl->addColumn(Transact::ATTR_AMOUNT, Ddl::TYPE_DECIMAL, '12,4', array('nullable' => false),
    'Payout amount.');
$tbl->addColumn(Transact::ATTR_CURR, Ddl::TYPE_CHAR, '3', array('nullable' => false),
    'Payout currency.');
$tbl->setComment('Atomic transactions.');
$conn->createTable($tbl);

/* Customer FK */
$fkName = $conn->getForeignKeyName(
    $tblTransact,
    Transact::ATTR_CUSTOMER_ID,
    $tblCustomer,
    Mage_Eav_Model_Entity::DEFAULT_ENTITY_ID_FIELD
);
$conn->addForeignKey(
    $fkName,
    $tblTransact,
    Transact::ATTR_CUSTOMER_ID,
    $tblCustomer,
    Mage_Eav_Model_Entity::DEFAULT_ENTITY_ID_FIELD,
    Db::FK_ACTION_RESTRICT,
    DB::FK_ACTION_RESTRICT
);


/**
 * Bonus Payout Transaction
 */

$optKey = array('primary' => true, 'nullable' => false, 'unsigned' => true);

$tbl = $conn->newTable($tblPayoutTransact);
$tbl->addColumn(PayoutTransact::ATTR_PAYOUT_ID, Ddl::TYPE_INTEGER, null, $optKey,
    'Payout ID.');
$tbl->addColumn(PayoutTransact::ATTR_TRANSACT_ID, Ddl::TYPE_INTEGER, null, $optKey,
    'Transaction ID.');
$tbl->setComment('Relations between transactions and payouts.');
$conn->createTable($tbl);

/* Payout FK */
$fkName = $conn->getForeignKeyName(
    $tblPayoutTransact,
    PayoutTransact::ATTR_PAYOUT_ID,
    $tblPayout,
    Payout::ATTR_ID
);
$conn->addForeignKey(
    $fkName,
    $tblPayoutTransact,
    PayoutTransact::ATTR_PAYOUT_ID,
    $tblPayout,
    Payout::ATTR_ID,
    Db::FK_ACTION_RESTRICT,
    DB::FK_ACTION_RESTRICT
);

/* Transact FK */
$fkName = $conn->getForeignKeyName(
    $tblPayoutTransact,
    PayoutTransact::ATTR_TRANSACT_ID,
    $tblTransact,
    Transact::ATTR_ID
);
$conn->addForeignKey(
    $fkName,
    $tblPayoutTransact,
    PayoutTransact::ATTR_TRANSACT_ID,
    $tblTransact,
    Transact::ATTR_ID,
    Db::FK_ACTION_RESTRICT,
    DB::FK_ACTION_RESTRICT
);


/* UQ index (transact_id) */
$ndxFields = array(PayoutTransact::ATTR_TRANSACT_ID);
$ndxName = $conn->getIndexName($tblPayoutTransact, $ndxFields, Db::INDEX_TYPE_UNIQUE);
$conn->addIndex($tblPayoutTransact, $ndxName, $ndxFields, Db::INDEX_TYPE_UNIQUE);


/**
 * Post setup Mage routines.
 */
$this->endSetup();