<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Mage_Eav_Model_Entity as EavEntity;
use Praxigento_Bonus_Config as Config;
use Praxigento_Bonus_Model_Own_Account as Account;
use Praxigento_Bonus_Model_Own_Balance as Balance;
use Praxigento_Bonus_Model_Own_Cfg_Personal as CfgPersonal;
use Praxigento_Bonus_Model_Own_Details_Retail as DetailsRetail;
use Praxigento_Bonus_Model_Own_Log_Account as LogAccount;
use Praxigento_Bonus_Model_Own_Log_Calc as LogCalc;
use Praxigento_Bonus_Model_Own_Log_Downline as LogDownline;
use Praxigento_Bonus_Model_Own_Log_Order as LogOrder;
use Praxigento_Bonus_Model_Own_Log_Payout as LogPayout;
use Praxigento_Bonus_Model_Own_Operation as Operation;
use Praxigento_Bonus_Model_Own_Period as Period;
use Praxigento_Bonus_Model_Own_Snap_Bonus as SnapBonus;
use Praxigento_Bonus_Model_Own_Snap_Downline as SnapDownline;
use Praxigento_Bonus_Model_Own_Transaction as Transaction;
use Praxigento_Bonus_Model_Own_Type_Asset as TypeAsset;
use Praxigento_Bonus_Model_Own_Type_Calc as TypeCalc;
use Praxigento_Bonus_Model_Own_Type_Oper as TypeOper;
use Praxigento_Bonus_Model_Own_Type_Period as TypePeriod;
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
$tblAccount = $this->getTable(Config::ENTITY_ACCOUNT);
$tblBalance = $this->getTable(Config::ENTITY_BALANCE);
$tblCfgPersonal = $this->getTable(Config::ENTITY_CFG_PERSONAL);
$tblDetailsRetail = $this->getTable(Config::ENTITY_DETAILS_RETAIL);
$tblLogAccount = $this->getTable(Config::ENTITY_LOG_ACCOUNT);
$tblLogCalc = $this->getTable(Config::ENTITY_LOG_CALC);
$tblLogDownline = $this->getTable(Config::ENTITY_LOG_DOWNLINE);
$tblLogOrder = $this->getTable(Config::ENTITY_LOG_ORDER);
$tblLogPayout = $this->getTable(Config::ENTITY_LOG_PAYOUT);
$tblOperation = $this->getTable(Config::ENTITY_OPERATION);
$tblPeriod = $this->getTable(Config::ENTITY_PERIOD);
$tblSnapBonus = $this->getTable(Config::ENTITY_SNAP_BONUS);
$tblSnapDownline = $this->getTable(Config::ENTITY_SNAP_DOWNLINE);
$tblTransaction = $this->getTable(Config::ENTITY_TRANSACTION);
$tblTypeAsset = $this->getTable(Config::ENTITY_TYPE_ASSET);
$tblTypeCalc = $this->getTable(Config::ENTITY_TYPE_CALC);
$tblTypeOper = $this->getTable(Config::ENTITY_TYPE_OPER);
$tblTypePeriod = $this->getTable(Config::ENTITY_TYPE_PERIOD);
/**
 * Mage tables names.
 */
$tblSalesOrder = $this->getTable('sales/order');
$tblCustomer = $this->getTable('customer/entity');

/** =================================================================================================================
 * Create tables.
 * =============================================================================================================== */
$optId = array( 'identity' => true, 'primary' => true, 'nullable' => false, 'unsigned' => true );
$currentTs = Varien_Db_Ddl_Table::TIMESTAMP_INIT;


/** ******************
 * Type Asset
 ****************** */
$tbl = $conn->newTable($tblTypeAsset);
$tbl->addColumn(TypeAsset::ATTR_ID, Ddl::TYPE_INTEGER, null, $optId,
    'Instance ID.');
$tbl->addColumn(TypeAsset::ATTR_CODE, Ddl::TYPE_TEXT, 255, array( 'nullable' => false ),
    'Code of the asset (pv, int, ext, intEUR, ...).');
$tbl->addColumn(TypeAsset::ATTR_NOTE, Ddl::TYPE_TEXT, 255, array( 'nullable' => false ),
    'Description of the asset(PV, internal money, ...).');
$tbl->setComment('Types of the available assets.');
$conn->createTable($tbl);
/* UQs  */
prxgt_install_create_index_unique($conn, $tblTypeAsset, array( TypeAsset::ATTR_CODE ));


/** ******************
 * Type Bonus
 ****************** */
$tbl = $conn->newTable($tblTypeCalc);
$tbl->addColumn(TypeCalc::ATTR_ID, Ddl::TYPE_INTEGER, null, $optId,
    'Instance ID.');
$tbl->addColumn(TypeCalc::ATTR_CODE, Ddl::TYPE_TEXT, 255, array( 'nullable' => false ),
    'Code of the bonus type (pv, gv, tv, ...).');
$tbl->addColumn(TypeCalc::ATTR_NOTE, Ddl::TYPE_TEXT, 255, array( 'nullable' => false ),
    'Description of the bonus type (Personal Volume, ...).');
$tbl->setComment('Types of the available bonuses.');
$conn->createTable($tbl);
/* UQs  */
prxgt_install_create_index_unique($conn, $tblTypeCalc, array( TypeCalc::ATTR_CODE ));


/** ******************
 * Type Oper
 ****************** */
$tbl = $conn->newTable($tblTypeOper);
$tbl->addColumn(TypeOper::ATTR_ID, Ddl::TYPE_INTEGER, null, $optId,
    'Instance ID.');
$tbl->addColumn(TypeOper::ATTR_CODE, Ddl::TYPE_TEXT, 255, array( 'nullable' => false ),
    'Code of the operation (int, ...).');
$tbl->addColumn(TypeOper::ATTR_NOTE, Ddl::TYPE_TEXT, 255, array( 'nullable' => false ),
    'Description of the operation (Internal Trnasfer, ...).');
$tbl->setComment('Types of the available operations.');
$conn->createTable($tbl);
/* UQs  */
prxgt_install_create_index_unique($conn, $tblTypeOper, array( TypeOper::ATTR_CODE ));


/** ******************
 * Type Period
 ****************** */
$tbl = $conn->newTable($tblTypePeriod);
$tbl->addColumn(TypePeriod::ATTR_ID, Ddl::TYPE_INTEGER, null, $optId,
    'Instance ID.');
$tbl->addColumn(TypePeriod::ATTR_CODE, Ddl::TYPE_TEXT, 255, array( 'nullable' => false ),
    'Code of the bonus calculation period (DAY, WEEK, ...).');
$tbl->addColumn(TypePeriod::ATTR_NOTE, Ddl::TYPE_TEXT, 255, array( 'nullable' => false ),
    'Description of the operation (Daily calculation, ...).');
$tbl->setComment('Types of the available bonus calculation periods.');
$conn->createTable($tbl);
/* UQs  */
prxgt_install_create_index_unique($conn, $tblTypePeriod, array( TypePeriod::ATTR_CODE ));


/** ******************
 * Account
 ****************** */
$tbl = $conn->newTable($tblAccount);
$tbl->addColumn(Account::ATTR_ID, Ddl::TYPE_INTEGER, null, $optId,
    'Instance ID.');
$tbl->addColumn(Account::ATTR_CUSTOMER_ID, Ddl::TYPE_INTEGER, null, array( 'nullable' => false, 'unsigned' => true ),
    'Customer related to the account.');
$tbl->addColumn(Account::ATTR_ASSET_ID, Ddl::TYPE_INTEGER, null, array( 'nullable' => false, 'unsigned' => true ),
    'Type of the accounted asset.');
$tbl->setComment('Customer accounts to register asset transition.');
$conn->createTable($tbl);
/* UQs  */
prxgt_install_create_index_unique($conn, $tblAccount, array( Account::ATTR_CUSTOMER_ID, Account::ATTR_ASSET_ID ));
/* FKs */
prxgt_install_create_foreign_key($conn, $tblAccount, Account::ATTR_CUSTOMER_ID, $tblCustomer, EavEntity::DEFAULT_ENTITY_ID_FIELD);
prxgt_install_create_foreign_key($conn, $tblAccount, Account::ATTR_ASSET_ID, $tblTypeAsset, TypeAsset::ATTR_ID);


/** ******************
 * Operation
 ****************** */
$tbl = $conn->newTable($tblOperation);
$tbl->addColumn(Operation::ATTR_ID, Ddl::TYPE_INTEGER, null, $optId,
    'Instance ID.');
$tbl->addColumn(Operation::ATTR_TYPE_ID, Ddl::TYPE_INTEGER, null, array( 'nullable' => false, 'unsigned' => true ),
    'Type of the operation.');
$tbl->addColumn(Operation::ATTR_DATE_PERFORMED, Ddl::TYPE_TIMESTAMP, null, array( 'nullable' => false, 'default' => $currentTs ),
    'Operation performed time.');
$tbl->setComment('Operations with assets (transactions set).');
$conn->createTable($tbl);
/* FKs */
prxgt_install_create_foreign_key($conn, $tblOperation, Operation::ATTR_TYPE_ID, $tblTypeOper, TypeOper::ATTR_ID);


/** ******************
 * Transaction
 ****************** */
$tbl = $conn->newTable($tblTransaction);
$tbl->addColumn(Transaction::ATTR_ID, Ddl::TYPE_INTEGER, null, $optId,
    'Instance ID.');
$tbl->addColumn(Transaction::ATTR_OPERATION_ID, Ddl::TYPE_INTEGER, null, array( 'nullable' => false, 'unsigned' => true ),
    'ID of the related operation.');
$tbl->addColumn(Transaction::ATTR_DATE_APPLIED, Ddl::TYPE_TIMESTAMP, null, array( 'nullable' => false, 'default' => $currentTs ),
    'Time transaction applied to the account balances (can be in the past).');
$tbl->addColumn(Transaction::ATTR_DEBIT_ACC_ID, Ddl::TYPE_INTEGER, null, array( 'nullable' => false, 'unsigned' => true ),
    'Debit account id.');
$tbl->addColumn(Transaction::ATTR_CREDIT_ACC_ID, Ddl::TYPE_INTEGER, null, array( 'nullable' => false, 'unsigned' => true ),
    'Credit account id.');
$tbl->addColumn(Transaction::ATTR_VALUE, Ddl::TYPE_DECIMAL, '12,4', array( 'nullable' => false ),
    'Change value (positive only).');
$tbl->setComment('Asset atomic transactions.');
$conn->createTable($tbl);
/* FKs */
prxgt_install_create_foreign_key($conn, $tblTransaction, Transaction::ATTR_OPERATION_ID, $tblOperation, Operation::ATTR_ID);
prxgt_install_create_foreign_key($conn, $tblTransaction, Transaction::ATTR_DEBIT_ACC_ID, $tblAccount, Account::ATTR_ID);
prxgt_install_create_foreign_key($conn, $tblTransaction, Transaction::ATTR_CREDIT_ACC_ID, $tblAccount, Account::ATTR_ID);


/** ******************
 * Balance
 ****************** */
$tbl = $conn->newTable($tblBalance);
$tbl->addColumn(Balance::ATTR_ID, Ddl::TYPE_INTEGER, null, $optId,
    'Instance ID.');
$tbl->addColumn(Balance::ATTR_ACCOUNT_ID, Ddl::TYPE_INTEGER, null, array( 'nullable' => false, 'unsigned' => true ),
    'Customer related to the account.');
$tbl->addColumn(Balance::ATTR_PERIOD, Ddl::TYPE_TEXT, '8', array( 'nullable' => false ),
    'Historical period in format [NOW|YYYY|YYYYMM|YYYYMMDD]');
$tbl->addColumn(Balance::ATTR_VALUE, Ddl::TYPE_DECIMAL, '12,4', array( 'nullable' => false ),
    'Current balance value (positive or negative).');
$tbl->setComment('Account balances (current and history).');
$conn->createTable($tbl);
/* UQs  */
prxgt_install_create_index_unique($conn, $tblBalance, array( Balance::ATTR_ACCOUNT_ID, Balance::ATTR_PERIOD ));
/* FKs */
prxgt_install_create_foreign_key($conn, $tblBalance, Balance::ATTR_ACCOUNT_ID, $tblAccount, Account::ATTR_ID);


/** ******************
 * Period
 ****************** */
$tbl = $conn->newTable($tblPeriod);
$tbl->addColumn(Period::ATTR_ID, Ddl::TYPE_INTEGER, null, $optId,
    'Instance ID.');
$tbl->addColumn(Period::ATTR_CALC_TYPE_ID, Ddl::TYPE_INTEGER, null, array( 'nullable' => false, 'unsigned' => true ),
    'Bonus type related to this period.');
$tbl->addColumn(Period::ATTR_TYPE, Ddl::TYPE_INTEGER, null, array( 'nullable' => false, 'unsigned' => true ),
    'Period type.');
$tbl->addColumn(Period::ATTR_VALUE, Ddl::TYPE_TEXT, '8', array( 'nullable' => false ),
    'Period value in format [YYYY|YYYYMM|YYYYMMDD]');
$tbl->setComment('Bonus calculation periods.');
$conn->createTable($tbl);
/* UQs  */
prxgt_install_create_index_unique($conn, $tblPeriod, array( Period::ATTR_CALC_TYPE_ID, Period::ATTR_VALUE ));
/* FKs */
prxgt_install_create_foreign_key($conn, $tblPeriod, Period::ATTR_CALC_TYPE_ID, $tblTypeCalc, TypeCalc::ATTR_ID);
prxgt_install_create_foreign_key($conn, $tblPeriod, Period::ATTR_TYPE, $tblTypePeriod, TypePeriod::ATTR_ID);


/** ******************
 * Cfg Personal
 ****************** */
$tbl = $conn->newTable($tblCfgPersonal);
$tbl->addColumn(CfgPersonal::ATTR_ID, Ddl::TYPE_INTEGER, null, $optId,
    'Instance ID.');
$tbl->addColumn(CfgPersonal::ATTR_LEVEL, Ddl::TYPE_DECIMAL, '12,4', array( 'nullable' => false ),
    'Low level of PV per period for applied percent (included).');
$tbl->addColumn(CfgPersonal::ATTR_PERCENT, Ddl::TYPE_DECIMAL, '12,4', array( 'nullable' => false ),
    'Percent applied to PV collected per period to compute bonus value.');
$tbl->setComment('Personal Volume bonus percent by PV level.');
$conn->createTable($tbl);


/** ******************
 * Detail Retail
 ****************** */
$tbl = $conn->newTable($tblDetailsRetail);
$tbl->addColumn(DetailsRetail::ATTR_ID, Ddl::TYPE_INTEGER, null, $optId,
    'Entity ID.');
$tbl->addColumn(DetailsRetail::ATTR_ORDER_ID, Ddl::TYPE_INTEGER, null, array( 'nullable' => false, 'unsigned' => true ),
    'Related order that generates bonus.');
$tbl->addColumn(DetailsRetail::ATTR_CUSTOMER_ID, Ddl::TYPE_INTEGER, null, array( 'nullable' => false, 'unsigned' => true ),
    'Customer that should earn this bonus (sponsor of the order creator).');
$tbl->addColumn(DetailsRetail::ATTR_CURR, Ddl::TYPE_TEXT, '3', array( 'nullable' => false ),
    'Bonus amount currency.');
$tbl->addColumn(DetailsRetail::ATTR_FEE, Ddl::TYPE_DECIMAL, '12,4', array( 'nullable' => false ),
    'Bonus fee value.');
$tbl->addColumn(DetailsRetail::ATTR_FEE_FIXED, Ddl::TYPE_DECIMAL, '12,4', array( 'nullable' => false ),
    'Fixed part of the bonus fee.');
$tbl->addColumn(DetailsRetail::ATTR_FEE_PERCENT, Ddl::TYPE_DECIMAL, '5,4', array( 'nullable' => false ),
    'Bonus fee percent.');
$tbl->addColumn(DetailsRetail::ATTR_FEE_MIN, Ddl::TYPE_DECIMAL, '12,4', array( 'nullable' => false ),
    'Minimum bonus fee.');
$tbl->addColumn(DetailsRetail::ATTR_FEE_MAX, Ddl::TYPE_DECIMAL, '12,4', array( 'nullable' => false ),
    'Maximum bonus fee.');
$tbl->setComment('Details for Retail bonus.');
$conn->createTable($tbl);

/* UQ index (order_id) */
prxgt_install_create_index_unique($conn, $tblDetailsRetail, array( DetailsRetail::ATTR_ORDER_ID ));

/* Order FK */
prxgt_install_create_foreign_key($conn, $tblDetailsRetail, DetailsRetail::ATTR_ORDER_ID, $tblSalesOrder, EavEntity::DEFAULT_ENTITY_ID_FIELD);
/* Customer FK */
prxgt_install_create_foreign_key($conn, $tblDetailsRetail, DetailsRetail::ATTR_CUSTOMER_ID, $tblCustomer, EavEntity::DEFAULT_ENTITY_ID_FIELD);


/** ******************
 * Log Account
 ****************** */
$tbl = $conn->newTable($tblLogAccount);
$tbl->addColumn(LogAccount::ATTR_ID, Ddl::TYPE_INTEGER, null, $optId,
    'Entity ID.');
$tbl->addColumn(LogAccount::ATTR_DATE_CHANGED, Ddl::TYPE_TIMESTAMP, null, array( 'nullable' => false, 'default' => $currentTs ),
    'Action performed time.');
$tbl->addColumn(LogAccount::ATTR_CUSTOMER_ID, Ddl::TYPE_INTEGER, null, array( 'nullable' => false, 'unsigned' => true ),
    'Action related customer.');
$tbl->addColumn(LogAccount::ATTR_VALUE, Ddl::TYPE_DECIMAL, '12,4', array( 'nullable' => false ),
    'Change value (positive or negative).');
$tbl->addColumn(LogAccount::ATTR_CURR, Ddl::TYPE_TEXT, '3', array( 'nullable' => false ),
    'Change value currency.');
$tbl->setComment('Log for account transfers.');
$conn->createTable($tbl);

/* Customer FK */
prxgt_install_create_foreign_key($conn, $tblLogAccount, LogAccount::ATTR_CUSTOMER_ID, $tblCustomer, EavEntity::DEFAULT_ENTITY_ID_FIELD);


/** ******************
 * Log Calculations
 ****************** */
$tbl = $conn->newTable($tblLogCalc);
$tbl->addColumn(LogCalc::ATTR_ID, Ddl::TYPE_INTEGER, null, $optId,
    'Entity ID.');
$tbl->addColumn(LogCalc::ATTR_DATE_PERFORMED, Ddl::TYPE_TIMESTAMP, null, array( 'nullable' => false, 'default' => $currentTs ),
    'Action performed time.');
$tbl->addColumn(LogCalc::ATTR_PERIOD_ID, Ddl::TYPE_INTEGER, null, array( 'nullable' => false, 'unsigned' => true ),
    'Calculation related period.');
$tbl->addColumn(LogCalc::ATTR_STATE, Ddl::TYPE_TEXT, 255, array( 'nullable' => false, 'default' => Config::STATE_PERIOD_PROCESSING ),
    'Calculation state (processing | complete | reverted).');
$tbl->setComment('Log for calculations.');
$conn->createTable($tbl);

/* Bonus type FK */
prxgt_install_create_foreign_key($conn, $tblLogCalc, LogCalc::ATTR_PERIOD_ID, $tblPeriod, Period::ATTR_ID);


/** ******************
 * Log Downline
 ****************** */
$tbl = $conn->newTable($tblLogDownline);
$tbl->addColumn(LogDownline ::ATTR_ID, Ddl::TYPE_INTEGER, null, $optId,
    'Entity ID.');
$tbl->addColumn(LogDownline::ATTR_DATE_CHANGED, Ddl::TYPE_TIMESTAMP, null, array( 'nullable' => false, 'default' => $currentTs ),
    'Action performed time.');
$tbl->addColumn(LogDownline::ATTR_CUSTOMER_ID, Ddl::TYPE_INTEGER, null, array( 'nullable' => false, 'unsigned' => true ),
    'Action related customer.');
$tbl->addColumn(LogDownline::ATTR_PARENT_ID, Ddl::TYPE_INTEGER, null, array( 'nullable' => false, 'unsigned' => true ),
    'New parent customer for action related customer.');
$tbl->setComment('Log for downline tree changes.');
$conn->createTable($tbl);

/* Customer FK */
prxgt_install_create_foreign_key($conn, $tblLogDownline, LogDownline::ATTR_CUSTOMER_ID, $tblCustomer, EavEntity::DEFAULT_ENTITY_ID_FIELD);
/* Upline type FK */
prxgt_install_create_foreign_key($conn, $tblLogDownline, LogDownline::ATTR_PARENT_ID, $tblCustomer, EavEntity::DEFAULT_ENTITY_ID_FIELD);


/** ******************
 * Log Order
 ****************** */
$tbl = $conn->newTable($tblLogOrder);
$tbl->addColumn(LogOrder ::ATTR_ID, Ddl::TYPE_INTEGER, null, $optId,
    'Entity ID.');
$tbl->addColumn(LogOrder::ATTR_DATE_CHANGED, Ddl::TYPE_TIMESTAMP, null, array( 'nullable' => false, 'default' => $currentTs ),
    'Action performed time.');
$tbl->addColumn(LogOrder::ATTR_ORDER_ID, Ddl::TYPE_INTEGER, null, array( 'nullable' => false, 'unsigned' => true ),
    'Action related sales order.');
$tbl->addColumn(LogOrder::ATTR_CALC_TYPE_ID, Ddl::TYPE_INTEGER, null, array( 'nullable' => false, 'unsigned' => true ),
    'Action related bonus type.');
$tbl->addColumn(LogOrder::ATTR_VALUE, Ddl::TYPE_DECIMAL, '12,4', array( 'nullable' => false ),
    'Change value (positive or negative).');
$tbl->setComment('Log for sales order related changes.');
$conn->createTable($tbl);

/* Order FK */
prxgt_install_create_foreign_key($conn, $tblLogOrder, LogOrder::ATTR_ORDER_ID, $tblSalesOrder, EavEntity::DEFAULT_ENTITY_ID_FIELD);
/* Bonus type FK */
prxgt_install_create_foreign_key($conn, $tblLogOrder, LogOrder::ATTR_CALC_TYPE_ID, $tblTypeCalc, TypeCalc::ATTR_ID);


/** ******************
 * Log Payout
 ****************** */
$tbl = $conn->newTable($tblLogPayout);
$tbl->addColumn(LogPayout::ATTR_ID, Ddl::TYPE_INTEGER, null, $optId,
    'Entity ID.');
$tbl->addColumn(LogPayout::ATTR_DATE_CHANGED, Ddl::TYPE_TIMESTAMP, null, array( 'nullable' => false, 'default' => $currentTs ),
    'Payout performed time.');
$tbl->addColumn(LogPayout::ATTR_CUSTOMER_ID, Ddl::TYPE_INTEGER, null, array( 'nullable' => false, 'unsigned' => true ),
    'Payout related customer.');
$tbl->addColumn(LogPayout::ATTR_VALUE, Ddl::TYPE_DECIMAL, '12,4', array( 'nullable' => false ),
    'Change value (positive or negative).');
$tbl->addColumn(LogPayout::ATTR_CURR, Ddl::TYPE_TEXT, '3', array( 'nullable' => false ),
    'Change value currency.');
$tbl->setComment('Log for payouts.');
$conn->createTable($tbl);

/* Customer FK */
prxgt_install_create_foreign_key($conn, $tblLogPayout, LogPayout::ATTR_CUSTOMER_ID, $tblCustomer, EavEntity::DEFAULT_ENTITY_ID_FIELD);


/** ******************
 * Snapshot Bonus
 ****************** */
$tbl = $conn->newTable($tblSnapBonus);
$tbl->addColumn(SnapBonus::ATTR_ID, Ddl::TYPE_INTEGER, null, $optId,
    'Entity ID.');
$tbl->addColumn(SnapBonus::ATTR_CUSTOMER_ID, Ddl::TYPE_INTEGER, null, array( 'nullable' => false, 'unsigned' => true ),
    'Related customer.');
$tbl->addColumn(SnapBonus::ATTR_CALC_TYPE_ID, Ddl::TYPE_INTEGER, null, array( 'nullable' => false, 'unsigned' => true ),
    'Bonus type.');
$tbl->addColumn(SnapBonus::ATTR_PERIOD, Ddl::TYPE_TEXT, '8', array( 'nullable' => false ),
    'Historical period in format [NOW|YYYY|YYYYMM|YYYYMMDD]');
$tbl->addColumn(SnapBonus::ATTR_VALUE, Ddl::TYPE_DECIMAL, '12,4', array( 'nullable' => false ),
    'Current bonus value (positive or negative).');
$tbl->setComment('Current state of the bonuses per customer.');
$conn->createTable($tbl);

/* Customer FK */
prxgt_install_create_foreign_key($conn, $tblSnapBonus, SnapBonus::ATTR_CUSTOMER_ID, $tblCustomer, EavEntity::DEFAULT_ENTITY_ID_FIELD);
/* Bonus type FK */
prxgt_install_create_foreign_key($conn, $tblSnapBonus, SnapBonus::ATTR_CALC_TYPE_ID, $tblTypeCalc, TypeCalc::ATTR_ID);


/** ******************
 * Snapshot Downline
 ****************** */
$tbl = $conn->newTable($tblSnapDownline);
$tbl->addColumn(SnapDownline::ATTR_PERIOD, Ddl::TYPE_TEXT, '8', array( 'primary' => true, 'identity' => false, 'nullable' => false ),
    'Historical period in format [NOW|YYYY|YYYYMM|YYYYMMDD]');
$tbl->addColumn(SnapDownline::ATTR_CUSTOMER_ID, Ddl::TYPE_INTEGER, null, array( 'primary' => true, 'identity' => false, 'nullable' => false, 'unsigned' => true ),
    'Customer itself.');
$tbl->addColumn(SnapDownline::ATTR_PARENT_ID, Ddl::TYPE_INTEGER, null, array( 'nullable' => false, 'unsigned' => true ),
    'Parent customer (sponsor, upline).');
$tbl->addColumn(SnapDownline::ATTR_PATH, Ddl::TYPE_TEXT, '255', array( 'nullable' => false ),
    'Path to the node - /1/2/3/.../');
$tbl->addColumn(SnapDownline::ATTR_NDX, Ddl::TYPE_INTEGER, null, array( 'nullable' => false ),
    'Node index in the whole tree.');
$tbl->addColumn(SnapDownline::ATTR_DEPTH, Ddl::TYPE_INTEGER, null, array( 'nullable' => false ),
    'Node depth from the root of the tree.');
$tbl->setComment('Current state of the downline tree.');
$conn->createTable($tbl);

/* NDX */
prxgt_install_create_index($conn, $tblSnapDownline, array( SnapDownline::ATTR_PERIOD ));
prxgt_install_create_index($conn, $tblSnapDownline, array( SnapDownline::ATTR_DEPTH ));
prxgt_install_create_index($conn, $tblSnapDownline, array( SnapDownline::ATTR_NDX ));
/* FKs */
prxgt_install_create_foreign_key($conn, $tblSnapDownline, SnapDownline::ATTR_CUSTOMER_ID, $tblCustomer, EavEntity::DEFAULT_ENTITY_ID_FIELD);
prxgt_install_create_foreign_key($conn, $tblSnapDownline, SnapDownline::ATTR_PARENT_ID, $tblCustomer, EavEntity::DEFAULT_ENTITY_ID_FIELD);


/** =================================================================================================================
 *  Insert initial data.
 * ================================================================================================================= */

/**
 * Asset Type data
 */
$conn->insertArray(
    $tblTypeAsset,
    array( TypeAsset::ATTR_CODE, TypeAsset::ATTR_NOTE ),
    array(
        array( Config::ASSET_EXT, 'External money (base currency).' ),
        array( Config::ASSET_INT, 'Internal money (base currency).' ),
        array( Config::ASSET_PV, 'PV (volume points).' ),
        array( Config::ASSET_RETAIL, 'Delayed retail bonus (base currency).' )
    )
);

/**
 * Operation Type data
 */
$conn->insertArray(
    $tblTypeOper,
    array( TypeOper::ATTR_CODE, TypeOper::ATTR_NOTE ),
    array(
        array( Config::OPER_BONUS_PV, 'PV Bonus enrollment.' ),
        array( Config::OPER_ORDER_PV, 'PV asset from order.' ),
        array( Config::OPER_ORDER_RETAIL, 'Retail asset from order.' ),
        array( Config::OPER_PV_FWRD, 'PV transfer for the same customer from one not closed period to other period in the future.' ),
        array( Config::OPER_PV_INT, 'PV transfer between customers.' ),
        array( Config::OPER_PV_WRITE_OFF, 'PV write off from customer accounts in the end of the PV bonus periods.' ),
        array( Config::OPER_TRANS_EXT_IN, 'Incoming transfer from external account to internal account of the customer.' ),
        array( Config::OPER_TRANS_EXT_OUT, 'Outgoing transfer from internal account to external account of the customer.' ),
        array( Config::OPER_TRANS_INT, 'Internal money transfer between customers.' )
    )
);

/**
 * Calculation Period Type data
 */
$conn->insertArray(
    $tblTypePeriod,
    array( TypePeriod::ATTR_CODE, TypePeriod::ATTR_NOTE ),
    array(
        array( Config::PERIOD_DAY, 'Daily calculation.' ),
        array( Config::PERIOD_WEEK, 'Weekly calculation.' ),
        array( Config::PERIOD_MONTH, 'Monthly calculation.' ),
        array( Config::PERIOD_YEAR, 'Yearly calculation.' ),
    )
);

/**
 * Calculation Types data
 */
$conn->insertArray(
    $tblTypeCalc,
    array( TypeCalc::ATTR_CODE, TypeCalc::ATTR_NOTE ),
    array(
        array( Config::CALC_BONUS_COURTESY, 'Courtesy bonus.' ),
        array( Config::CALC_BONUS_GROUP, 'Group bonus.' ),
        array( Config::CALC_BONUS_INFINITY, 'Infinity bonus.' ),
        array( Config::CALC_BONUS_OVERRIDE, 'Override bonus.' ),
        array( Config::CALC_BONUS_PERSONAL, 'Personal Volume bonus.' ),
        array( Config::CALC_BONUS_RETAIL, 'Retail bonus.' ),
        array( Config::CALC_BONUS_TEAM, 'Team volume bonus.' ),
        array( Config::CALC_PV_WRITE_OFF, 'PV write off calculation.' )
    )
);

/**
 * Cfg Personal data
 */
$conn->insertArray(
    $tblCfgPersonal,
    array( CfgPersonal::ATTR_LEVEL, CfgPersonal::ATTR_PERCENT ),
    array(
        array( '0.00', '0.00' ),
        array( '50.00', '0.05' ),
        array( '100.00', '0.10' ),
        array( '500.00', '0.15' ),
        array( '750.00', '0.20' )
    )
);


/**
 * Post setup Mage routines.
 */
$this->endSetup();