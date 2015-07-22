<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;
use Praxigento_Bonus_Model_Own_Core_Type as CoreType;
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
 * Own tables names.
 */
$tblCoreType = $this->getTable(Config::CFG_MODEL . '/' . Config::ENTITY_CORE_TYPE);
$tblDetailOrder = $this->getTable(Config::CFG_MODEL . '/' . Config::ENTITY_DETAIL_ORDER);
$tblLogAccount = $this->getTable(Config::CFG_MODEL . '/' . Config::ENTITY_LOG_ACCOUNT);
$tblLogBonus = $this->getTable(Config::CFG_MODEL . '/' . Config::ENTITY_LOG_BONUS);
$tblLogDowline = $this->getTable(Config::CFG_MODEL . '/' . Config::ENTITY_LOG_DOWNLINE);
$tblLogOrder = $this->getTable(Config::CFG_MODEL . '/' . Config::ENTITY_LOG_ORDER);
$tblLogPayout = $this->getTable(Config::CFG_MODEL . '/' . Config::ENTITY_LOG_PAYOUT);
$tblSnapBonus = $this->getTable(Config::CFG_MODEL . '/' . Config::ENTITY_SNAP_BONUS);
$tblSnapBonusHist = $this->getTable(Config::CFG_MODEL . '/' . Config::ENTITY_SNAP_BONUS_HIST);
$tblSnapDownline = $this->getTable(Config::CFG_MODEL . '/' . Config::ENTITY_SNAP_DOWNLINE);
$tblSnapDownlineHist = $this->getTable(Config::CFG_MODEL . '/' . Config::ENTITY_SNAP_DOWNLINE_HIST);
/**
 * Mage tables names.
 */
$tblSalesOrder = $this->getTable('sales/order');
$tblCustomer = $this->getTable('customer/entity');

/** =================================================================================================================
 * Create tables.
 * =============================================================================================================== */
$optId = array('identity' => true, 'primary' => true, 'nullable' => false, 'unsigned' => true);

/**
 * Core Type
 */
$tbl = $conn->newTable($tblCoreType);
$tbl->addColumn(CoreType::ATTR_ID, Ddl::TYPE_INTEGER, null, $optId,
    'Entity ID.');
$tbl->addColumn(CoreType::ATTR_CODE, Ddl::TYPE_CHAR, 255, array('nullable' => false),
    'Code of the bonus type (pv, gv, tv, ...)');
$tbl->addColumn(CoreType::ATTR_NOTE, Ddl::TYPE_CHAR, 255, array('nullable' => false),
    'Description of the bonus type (Personal Volume, ...)');
$tbl->setComment('Available bonus types.');
$conn->createTable($tbl);

/* UQ index (code) */
$ndxFields = array(CoreType::ATTR_CODE);
$ndxName = $conn->getIndexName($tblCoreType, $ndxFields, Db::INDEX_TYPE_UNIQUE);
$conn->addIndex($tblCoreType, $ndxName, $ndxFields, Db::INDEX_TYPE_UNIQUE);


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
$tbl->addColumn(Payout::ATTR_DESC, Ddl::TYPE_CHAR, 255, array('nullable' => false),
    'Payout description.');
$tbl->addColumn(Payout::ATTR_DATE_PAID, Ddl::TYPE_TIMESTAMP, null, array('nullable' => true),
    'Payment date.');
$tbl->addColumn(Payout::ATTR_REFERENCE, Ddl::TYPE_CHAR, 255, array('nullable' => true),
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

/* UQ index (reference) */
$ndxFields = array(Payout::ATTR_REFERENCE);
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
    'Transaction creation date.');
$tbl->addColumn(Transact::ATTR_AMOUNT, Ddl::TYPE_DECIMAL, '12,4', array('nullable' => false),
    'Transaction amount.');
$tbl->addColumn(Transact::ATTR_CURR, Ddl::TYPE_CHAR, '3', array('nullable' => false),
    'Transaction currency.');
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