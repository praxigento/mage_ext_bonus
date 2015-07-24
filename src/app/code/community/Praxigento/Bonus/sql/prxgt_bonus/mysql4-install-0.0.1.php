<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;
use Praxigento_Bonus_Model_Own_Core_Type as CoreType;
use Praxigento_Bonus_Model_Own_Details_Retail as DetailsRetail;
use Praxigento_Bonus_Model_Own_Log_Account as LogAccount;
use Praxigento_Bonus_Model_Own_Log_Bonus as LogBonus;
use Praxigento_Bonus_Model_Own_Log_Downline as LogDownline;
use Praxigento_Bonus_Model_Own_Log_Order as LogOrder;
use Praxigento_Bonus_Model_Own_Log_Payout as LogPayout;
use Praxigento_Bonus_Model_Own_Snap_Bonus as SnapBonus;
use Praxigento_Bonus_Model_Own_Snap_Bonus_Hist as SnapBonusHist;
use Praxigento_Bonus_Model_Own_Snap_Downline as SnapDownline;
use Praxigento_Bonus_Model_Own_Snap_Downline_Hist as SnapDownlineHist;
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
$tblDetailsRetail = $this->getTable(Config::CFG_MODEL . '/' . Config::ENTITY_DETAILS_RETAIL);
$tblLogAccount = $this->getTable(Config::CFG_MODEL . '/' . Config::ENTITY_LOG_ACCOUNT);
$tblLogBonus = $this->getTable(Config::CFG_MODEL . '/' . Config::ENTITY_LOG_BONUS);
$tblLogDownline = $this->getTable(Config::CFG_MODEL . '/' . Config::ENTITY_LOG_DOWNLINE);
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
$currentTs = Varien_Db_Ddl_Table::TIMESTAMP_INIT;

/** ******************
 * Core Type
 ****************** */
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


/** ******************
 * Detail Retail
 ****************** */
$tbl = $conn->newTable($tblDetailsRetail);
$tbl->addColumn(DetailsRetail::ATTR_ID, Ddl::TYPE_INTEGER, null, $optId,
    'Entity ID.');
$tbl->addColumn(DetailsRetail::ATTR_ORDER_ID, Ddl::TYPE_INTEGER, null, array('nullable' => false, 'unsigned' => true),
    'Related order that generates bonus.');
$tbl->addColumn(DetailsRetail::ATTR_CUSTOMER_ID, Ddl::TYPE_INTEGER, null, array('nullable' => false, 'unsigned' => true),
    'Customer that should earn this bonus (sponsor of the order creator).');
$tbl->addColumn(DetailsRetail::ATTR_CURR, Ddl::TYPE_CHAR, '3', array('nullable' => false),
    'Bonus amount currency.');
$tbl->addColumn(DetailsRetail::ATTR_FEE, Ddl::TYPE_DECIMAL, '12,4', array('nullable' => false),
    'Bonus fee value.');
$tbl->addColumn(DetailsRetail::ATTR_FEE_FIXED, Ddl::TYPE_DECIMAL, '12,4', array('nullable' => false),
    'Fixed part of the bonus fee.');
$tbl->addColumn(DetailsRetail::ATTR_FEE_PERCENT, Ddl::TYPE_DECIMAL, '5,4', array('nullable' => false),
    'Bonus fee percent.');
$tbl->addColumn(DetailsRetail::ATTR_FEE_MIN, Ddl::TYPE_DECIMAL, '12,4', array('nullable' => false),
    'Minimum bonus fee.');
$tbl->addColumn(DetailsRetail::ATTR_FEE_MAX, Ddl::TYPE_DECIMAL, '12,4', array('nullable' => false),
    'Maximum bonus fee.');
$conn->createTable($tbl);

/* UQ index (order_id) */
$ndxFields = array(DetailsRetail::ATTR_ORDER_ID);
$ndxName = $conn->getIndexName($tblDetailsRetail, $ndxFields, Db::INDEX_TYPE_UNIQUE);
$conn->addIndex($tblDetailsRetail, $ndxName, $ndxFields, Db::INDEX_TYPE_UNIQUE);

/* Order FK */
$fkName = $conn->getForeignKeyName(
    $tblDetailsRetail,
    DetailsRetail::ATTR_ORDER_ID,
    $tblSalesOrder,
    Mage_Eav_Model_Entity::DEFAULT_ENTITY_ID_FIELD
);
$conn->addForeignKey(
    $fkName,
    $tblDetailsRetail,
    DetailsRetail::ATTR_ORDER_ID,
    $tblSalesOrder,
    Mage_Eav_Model_Entity::DEFAULT_ENTITY_ID_FIELD,
    Db::FK_ACTION_RESTRICT,
    DB::FK_ACTION_RESTRICT
);

/* Customer FK */
$fkName = $conn->getForeignKeyName(
    $tblDetailsRetail,
    DetailsRetail::ATTR_CUSTOMER_ID,
    $tblCustomer,
    Mage_Eav_Model_Entity::DEFAULT_ENTITY_ID_FIELD
);
$conn->addForeignKey(
    $fkName,
    $tblDetailsRetail,
    DetailsRetail::ATTR_CUSTOMER_ID,
    $tblCustomer,
    Mage_Eav_Model_Entity::DEFAULT_ENTITY_ID_FIELD,
    Db::FK_ACTION_RESTRICT,
    DB::FK_ACTION_RESTRICT
);


/** ******************
 * Log Account
 ****************** */
$tbl = $conn->newTable($tblLogAccount);
$tbl->addColumn(LogAccount::ATTR_ID, Ddl::TYPE_INTEGER, null, $optId,
    'Entity ID.');
$tbl->addColumn(LogAccount::ATTR_DATE_CHANGED, Ddl::TYPE_TIMESTAMP, null, array('nullable' => false, 'default' => $currentTs),
    'Action performed time.');
$tbl->addColumn(LogAccount::ATTR_CUSTOMER_ID, Ddl::TYPE_INTEGER, null, array('nullable' => false, 'unsigned' => true),
    'Action related customer.');
$tbl->addColumn(LogAccount::ATTR_VALUE, Ddl::TYPE_DECIMAL, '12,4', array('nullable' => false),
    'Change value (positive or negative).');
$tbl->addColumn(LogAccount::ATTR_CURR, Ddl::TYPE_CHAR, '3', array('nullable' => false),
    'Change value currency.');
$conn->createTable($tbl);

/* Customer FK */
$fkName = $conn->getForeignKeyName(
    $tblLogAccount,
    LogAccount::ATTR_CUSTOMER_ID,
    $tblCustomer,
    Mage_Eav_Model_Entity::DEFAULT_ENTITY_ID_FIELD
);
$conn->addForeignKey(
    $fkName,
    $tblLogAccount,
    LogAccount::ATTR_CUSTOMER_ID,
    $tblCustomer,
    Mage_Eav_Model_Entity::DEFAULT_ENTITY_ID_FIELD,
    Db::FK_ACTION_RESTRICT,
    DB::FK_ACTION_RESTRICT
);


/** ******************
 * Log Bonus
 ****************** */
$tbl = $conn->newTable($tblLogBonus);
$tbl->addColumn(LogBonus::ATTR_ID, Ddl::TYPE_INTEGER, null, $optId,
    'Entity ID.');
$tbl->addColumn(LogBonus::ATTR_DATE_CHANGED, Ddl::TYPE_TIMESTAMP, null, array('nullable' => false, 'default' => $currentTs),
    'Action performed time.');
$tbl->addColumn(LogBonus::ATTR_CUSTOMER_ID, Ddl::TYPE_INTEGER, null, array('nullable' => false, 'unsigned' => true),
    'Action related customer.');
$tbl->addColumn(LogBonus::ATTR_TYPE_ID, Ddl::TYPE_INTEGER, null, array('nullable' => false, 'unsigned' => true),
    'Action related bonus type.');
$tbl->addColumn(LogBonus::ATTR_VALUE, Ddl::TYPE_DECIMAL, '12,4', array('nullable' => false),
    'Change value (positive or negative).');
$conn->createTable($tbl);

/* Customer FK */
$fkName = $conn->getForeignKeyName(
    $tblLogBonus,
    LogBonus::ATTR_CUSTOMER_ID,
    $tblCustomer,
    Mage_Eav_Model_Entity::DEFAULT_ENTITY_ID_FIELD
);
$conn->addForeignKey(
    $fkName,
    $tblLogBonus,
    LogBonus::ATTR_CUSTOMER_ID,
    $tblCustomer,
    Mage_Eav_Model_Entity::DEFAULT_ENTITY_ID_FIELD,
    Db::FK_ACTION_RESTRICT,
    DB::FK_ACTION_RESTRICT
);

/* Bonus type FK */
$fkName = $conn->getForeignKeyName(
    $tblLogBonus,
    LogBonus::ATTR_TYPE_ID,
    $tblCoreType,
    CoreType::ATTR_ID
);
$conn->addForeignKey(
    $fkName,
    $tblLogBonus,
    LogBonus::ATTR_TYPE_ID,
    $tblCoreType,
    CoreType::ATTR_ID,
    Db::FK_ACTION_RESTRICT,
    DB::FK_ACTION_RESTRICT
);


/** ******************
 * Log Downline
 ****************** */
$tbl = $conn->newTable($tblLogDownline);
$tbl->addColumn(LogDownline ::ATTR_ID, Ddl::TYPE_INTEGER, null, $optId,
    'Entity ID.');
$tbl->addColumn(LogDownline::ATTR_DATE_CHANGED, Ddl::TYPE_TIMESTAMP, null, array('nullable' => false, 'default' => $currentTs),
    'Action performed time.');
$tbl->addColumn(LogDownline::ATTR_CUSTOMER_ID, Ddl::TYPE_INTEGER, null, array('nullable' => false, 'unsigned' => true),
    'Action related customer.');
$tbl->addColumn(LogDownline::ATTR_PARENT_ID, Ddl::TYPE_INTEGER, null, array('nullable' => false, 'unsigned' => true),
    'New parent customer for action related customer.');
$conn->createTable($tbl);

/* Customer FK */
$fkName = $conn->getForeignKeyName(
    $tblLogDownline,
    LogDownline::ATTR_CUSTOMER_ID,
    $tblCustomer,
    Mage_Eav_Model_Entity::DEFAULT_ENTITY_ID_FIELD
);
$conn->addForeignKey(
    $fkName,
    $tblLogDownline,
    LogDownline::ATTR_CUSTOMER_ID,
    $tblCustomer,
    Mage_Eav_Model_Entity::DEFAULT_ENTITY_ID_FIELD,
    Db::FK_ACTION_RESTRICT,
    DB::FK_ACTION_RESTRICT
);

/* Upline type FK */
$fkName = $conn->getForeignKeyName(
    $tblLogDownline,
    LogDownline::ATTR_PARENT_ID,
    $tblCustomer,
    Mage_Eav_Model_Entity::DEFAULT_ENTITY_ID_FIELD
);
$conn->addForeignKey(
    $fkName,
    $tblLogDownline,
    LogDownline::ATTR_PARENT_ID,
    $tblCustomer,
    Mage_Eav_Model_Entity::DEFAULT_ENTITY_ID_FIELD,
    Db::FK_ACTION_RESTRICT,
    DB::FK_ACTION_RESTRICT
);


/** ******************
 * Log Order
 ****************** */
$tbl = $conn->newTable($tblLogOrder);
$tbl->addColumn(LogOrder ::ATTR_ID, Ddl::TYPE_INTEGER, null, $optId,
    'Entity ID.');
$tbl->addColumn(LogOrder::ATTR_DATE_CHANGED, Ddl::TYPE_TIMESTAMP, null, array('nullable' => false, 'default' => $currentTs),
    'Action performed time.');
$tbl->addColumn(LogOrder::ATTR_ORDER_ID, Ddl::TYPE_INTEGER, null, array('nullable' => false, 'unsigned' => true),
    'Action related sales order.');
$tbl->addColumn(LogOrder::ATTR_TYPE_ID, Ddl::TYPE_INTEGER, null, array('nullable' => false, 'unsigned' => true),
    'Action related bonus type.');
$tbl->addColumn(LogOrder::ATTR_VALUE, Ddl::TYPE_DECIMAL, '12,4', array('nullable' => false),
    'Change value (positive or negative).');
$conn->createTable($tbl);

/* Order FK */
$fkName = $conn->getForeignKeyName(
    $tblLogOrder,
    LogOrder::ATTR_ORDER_ID,
    $tblSalesOrder,
    Mage_Eav_Model_Entity::DEFAULT_ENTITY_ID_FIELD
);
$conn->addForeignKey(
    $fkName,
    $tblLogOrder,
    LogOrder::ATTR_ORDER_ID,
    $tblSalesOrder,
    Mage_Eav_Model_Entity::DEFAULT_ENTITY_ID_FIELD,
    Db::FK_ACTION_RESTRICT,
    DB::FK_ACTION_RESTRICT
);

/* Bonus type FK */
$fkName = $conn->getForeignKeyName(
    $tblLogOrder,
    LogOrder::ATTR_TYPE_ID,
    $tblCoreType,
    CoreType::ATTR_ID
);
$conn->addForeignKey(
    $fkName,
    $tblLogOrder,
    LogOrder::ATTR_TYPE_ID,
    $tblCoreType,
    CoreType::ATTR_ID,
    Db::FK_ACTION_RESTRICT,
    DB::FK_ACTION_RESTRICT
);


/** ******************
 * Log Payout
 ****************** */
$tbl = $conn->newTable($tblLogPayout);
$tbl->addColumn(LogPayout::ATTR_ID, Ddl::TYPE_INTEGER, null, $optId,
    'Entity ID.');
$tbl->addColumn(LogPayout::ATTR_DATE_CHANGED, Ddl::TYPE_TIMESTAMP, null, array('nullable' => false, 'default' => $currentTs),
    'Payout performed time.');
$tbl->addColumn(LogPayout::ATTR_CUSTOMER_ID, Ddl::TYPE_INTEGER, null, array('nullable' => false, 'unsigned' => true),
    'Payout related customer.');
$tbl->addColumn(LogPayout::ATTR_VALUE, Ddl::TYPE_DECIMAL, '12,4', array('nullable' => false),
    'Change value (positive or negative).');
$tbl->addColumn(LogPayout::ATTR_CURR, Ddl::TYPE_CHAR, '3', array('nullable' => false),
    'Change value currency.');
$conn->createTable($tbl);

/* Customer FK */
$fkName = $conn->getForeignKeyName(
    $tblLogPayout,
    LogPayout::ATTR_CUSTOMER_ID,
    $tblCustomer,
    Mage_Eav_Model_Entity::DEFAULT_ENTITY_ID_FIELD
);
$conn->addForeignKey(
    $fkName,
    $tblLogPayout,
    LogPayout::ATTR_CUSTOMER_ID,
    $tblCustomer,
    Mage_Eav_Model_Entity::DEFAULT_ENTITY_ID_FIELD,
    Db::FK_ACTION_RESTRICT,
    DB::FK_ACTION_RESTRICT
);


/** ******************
 * Snapshot Bonus
 ****************** */
$tbl = $conn->newTable($tblSnapBonus);
$tbl->addColumn(SnapBonus::ATTR_ID, Ddl::TYPE_INTEGER, null, $optId,
    'Entity ID.');
$tbl->addColumn(SnapBonus::ATTR_CUSTOMER_ID, Ddl::TYPE_INTEGER, null, array('nullable' => false, 'unsigned' => true),
    'Related customer.');
$tbl->addColumn(SnapBonus::ATTR_TYPE_ID, Ddl::TYPE_INTEGER, null, array('nullable' => false, 'unsigned' => true),
    'Bonus type.');
$tbl->addColumn(SnapBonus::ATTR_VALUE, Ddl::TYPE_DECIMAL, '12,4', array('nullable' => false),
    'Current bonus value (positive or negative).');
$conn->createTable($tbl);

/* Customer FK */
$fkName = $conn->getForeignKeyName(
    $tblSnapBonus,
    SnapBonus::ATTR_CUSTOMER_ID,
    $tblCustomer,
    Mage_Eav_Model_Entity::DEFAULT_ENTITY_ID_FIELD
);
$conn->addForeignKey(
    $fkName,
    $tblSnapBonus,
    SnapBonus::ATTR_CUSTOMER_ID,
    $tblCustomer,
    Mage_Eav_Model_Entity::DEFAULT_ENTITY_ID_FIELD,
    Db::FK_ACTION_RESTRICT,
    DB::FK_ACTION_RESTRICT
);

/* Bonus type FK */
$fkName = $conn->getForeignKeyName(
    $tblSnapBonus,
    SnapBonus::ATTR_TYPE_ID,
    $tblCoreType,
    CoreType::ATTR_ID
);
$conn->addForeignKey(
    $fkName,
    $tblSnapBonus,
    SnapBonus::ATTR_TYPE_ID,
    $tblCoreType,
    CoreType::ATTR_ID,
    Db::FK_ACTION_RESTRICT,
    DB::FK_ACTION_RESTRICT
);


/** ******************
 * Snapshot Bonus History
 ****************** */
$tbl = $conn->newTable($tblSnapBonusHist);
$tbl->addColumn(SnapBonusHist::ATTR_ID, Ddl::TYPE_INTEGER, null, $optId,
    'Entity ID.');
$tbl->addColumn(SnapBonusHist::ATTR_CUSTOMER_ID, Ddl::TYPE_INTEGER, null, array('nullable' => false, 'unsigned' => true),
    'Related customer.');
$tbl->addColumn(SnapBonusHist::ATTR_TYPE_ID, Ddl::TYPE_INTEGER, null, array('nullable' => false, 'unsigned' => true),
    'Bonus type.');
$tbl->addColumn(SnapBonusHist::ATTR_PERIOD, Ddl::TYPE_CHAR, '8', array('nullable' => false),
    'Historical period in format YYYYMM or YYYYMMDD');
$tbl->addColumn(SnapBonusHist::ATTR_VALUE, Ddl::TYPE_DECIMAL, '12,4', array('nullable' => false),
    'Current bonus value (positive or negative).');
$conn->createTable($tbl);

/* UQ index (order_id) */
$ndxFields = array(SnapBonusHist::ATTR_PERIOD, SnapBonusHist::ATTR_CUSTOMER_ID, SnapBonusHist::ATTR_TYPE_ID);
$ndxName = $conn->getIndexName($tblSnapBonusHist, $ndxFields, Db::INDEX_TYPE_UNIQUE);
$conn->addIndex($tblSnapBonusHist, $ndxName, $ndxFields, Db::INDEX_TYPE_UNIQUE);

/* Customer FK */
$fkName = $conn->getForeignKeyName(
    $tblSnapBonusHist,
    SnapBonusHist::ATTR_CUSTOMER_ID,
    $tblCustomer,
    Mage_Eav_Model_Entity::DEFAULT_ENTITY_ID_FIELD
);
$conn->addForeignKey(
    $fkName,
    $tblSnapBonusHist,
    SnapBonusHist::ATTR_CUSTOMER_ID,
    $tblCustomer,
    Mage_Eav_Model_Entity::DEFAULT_ENTITY_ID_FIELD,
    Db::FK_ACTION_RESTRICT,
    DB::FK_ACTION_RESTRICT
);

/* Bonus type FK */
$fkName = $conn->getForeignKeyName(
    $tblSnapBonusHist,
    SnapBonusHist::ATTR_TYPE_ID,
    $tblCoreType,
    CoreType::ATTR_ID
);
$conn->addForeignKey(
    $fkName,
    $tblSnapBonusHist,
    SnapBonusHist::ATTR_TYPE_ID,
    $tblCoreType,
    CoreType::ATTR_ID,
    Db::FK_ACTION_RESTRICT,
    DB::FK_ACTION_RESTRICT
);


/** ******************
 * Snapshot Downline
 ****************** */
$tbl = $conn->newTable($tblSnapDownline);
$tbl->addColumn(SnapDownline::ATTR_ID, Ddl::TYPE_INTEGER, null, $optId,
    'Entity ID.');
$tbl->addColumn(SnapDownline::ATTR_CUSTOMER_ID, Ddl::TYPE_INTEGER, null, array('nullable' => false, 'unsigned' => true),
    'Customer itself.');
$tbl->addColumn(SnapDownline::ATTR_PARENT_ID, Ddl::TYPE_INTEGER, null, array('nullable' => false, 'unsigned' => true),
    'Parent customer (sponsor, upline).');
$tbl->addColumn(SnapDownline::ATTR_PATH, Ddl::TYPE_CHAR, '255', array('nullable' => false),
    'Path to the node - /1/2/3/.../');
$conn->createTable($tbl);

/* Customer FK */
$fkName = $conn->getForeignKeyName(
    $tblSnapDownline,
    SnapDownline::ATTR_CUSTOMER_ID,
    $tblCustomer,
    Mage_Eav_Model_Entity::DEFAULT_ENTITY_ID_FIELD
);
$conn->addForeignKey(
    $fkName,
    $tblSnapDownline,
    SnapDownline::ATTR_CUSTOMER_ID,
    $tblCustomer,
    Mage_Eav_Model_Entity::DEFAULT_ENTITY_ID_FIELD,
    Db::FK_ACTION_RESTRICT,
    DB::FK_ACTION_RESTRICT
);

/* Parent customer FK */
$fkName = $conn->getForeignKeyName(
    $tblSnapDownline,
    SnapDownline::ATTR_PARENT_ID,
    $tblCustomer,
    Mage_Eav_Model_Entity::DEFAULT_ENTITY_ID_FIELD
);
$conn->addForeignKey(
    $fkName,
    $tblSnapDownline,
    SnapDownline::ATTR_PARENT_ID,
    $tblCustomer,
    Mage_Eav_Model_Entity::DEFAULT_ENTITY_ID_FIELD,
    Db::FK_ACTION_RESTRICT,
    DB::FK_ACTION_RESTRICT
);


/** ******************
 * Snapshot Downline History
 ****************** */
$tbl = $conn->newTable($tblSnapDownlineHist);
$tbl->addColumn(SnapDownlineHist::ATTR_ID, Ddl::TYPE_INTEGER, null, $optId,
    'Entity ID.');
$tbl->addColumn(SnapDownlineHist::ATTR_CUSTOMER_ID, Ddl::TYPE_INTEGER, null, array('nullable' => false, 'unsigned' => true),
    'Customer itself.');
$tbl->addColumn(SnapDownlineHist::ATTR_PARENT_ID, Ddl::TYPE_INTEGER, null, array('nullable' => false, 'unsigned' => true),
    'Parent customer (sponsor, upline).');
$tbl->addColumn(SnapDownlineHist::ATTR_PERIOD, Ddl::TYPE_CHAR, '8', array('nullable' => false),
    'Historical period in format YYYYMM or YYYYMMDD');
$tbl->addColumn(SnapDownlineHist::ATTR_PATH, Ddl::TYPE_CHAR, '255', array('nullable' => false),
    'Path to the node - /1/2/3/.../');
$conn->createTable($tbl);

/* UQ index (order_id) */
$ndxFields = array(SnapDownlineHist::ATTR_PERIOD, SnapDownlineHist::ATTR_CUSTOMER_ID, SnapDownlineHist::ATTR_UPLINE_ID);
$ndxName = $conn->getIndexName($tblSnapDownlineHist, $ndxFields, Db::INDEX_TYPE_UNIQUE);
$conn->addIndex($tblSnapDownlineHist, $ndxName, $ndxFields, Db::INDEX_TYPE_UNIQUE);

/* Customer FK */
$fkName = $conn->getForeignKeyName(
    $tblSnapDownlineHist,
    SnapDownlineHist::ATTR_CUSTOMER_ID,
    $tblCustomer,
    Mage_Eav_Model_Entity::DEFAULT_ENTITY_ID_FIELD
);
$conn->addForeignKey(
    $fkName,
    $tblSnapDownlineHist,
    SnapDownlineHist::ATTR_CUSTOMER_ID,
    $tblCustomer,
    Mage_Eav_Model_Entity::DEFAULT_ENTITY_ID_FIELD,
    Db::FK_ACTION_RESTRICT,
    DB::FK_ACTION_RESTRICT
);

/* Upline customer FK */
$fkName = $conn->getForeignKeyName(
    $tblSnapDownlineHist,
    SnapDownlineHist::ATTR_PARENT_ID,
    $tblCustomer,
    Mage_Eav_Model_Entity::DEFAULT_ENTITY_ID_FIELD
);
$conn->addForeignKey(
    $fkName,
    $tblSnapDownlineHist,
    SnapDownlineHist::ATTR_PARENT_ID,
    $tblCustomer,
    Mage_Eav_Model_Entity::DEFAULT_ENTITY_ID_FIELD,
    Db::FK_ACTION_RESTRICT,
    DB::FK_ACTION_RESTRICT
);

/**
 * Post setup Mage routines.
 */
$this->endSetup();