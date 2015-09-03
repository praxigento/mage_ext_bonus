<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
include_once('phpunit_bootstrap.php');

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Test_Config_UnitTest extends PHPUnit_Framework_TestCase {


    /**
     * Reset Config before each test.
     */
    public function setUp() {
        Config::set(null);
    }

    public function test_collections() {
        $cfg = Config::get();
        $this->assertTrue($cfg->collectionAccount() instanceof Praxigento_Bonus_Resource_Own_Account_Collection);
        $this->assertTrue($cfg->collectionBalance() instanceof Praxigento_Bonus_Resource_Own_Balance_Collection);
        $this->assertTrue($cfg->collectionLogCalc() instanceof Praxigento_Bonus_Resource_Own_Log_Calc_Collection);
        $this->assertTrue($cfg->collectionOperation() instanceof Praxigento_Bonus_Resource_Own_Operation_Collection);
        $this->assertTrue($cfg->collectionPeriod() instanceof Praxigento_Bonus_Resource_Own_Period_Collection);
        $this->assertTrue($cfg->collectionTransaction() instanceof Praxigento_Bonus_Resource_Own_Transaction_Collection);
        $this->assertTrue($cfg->collectionTypeAsset() instanceof Praxigento_Bonus_Resource_Own_Type_Asset_Collection);
        $this->assertTrue($cfg->collectionTypeCalc() instanceof Praxigento_Bonus_Resource_Own_Type_Calc_Collection);
        $this->assertTrue($cfg->collectionTypeOper() instanceof Praxigento_Bonus_Resource_Own_Type_Oper_Collection);
        $this->assertTrue($cfg->collectionTypePeriod() instanceof Praxigento_Bonus_Resource_Own_Type_Period_Collection);
    }


    public function test_helpers() {
        $cfg = Config::get();
        $this->assertTrue($cfg->helper() instanceof Praxigento_Bonus_Helper_Data);
        $this->assertTrue($cfg->helperAccount() instanceof Praxigento_Bonus_Helper_Account);
        $this->assertTrue($cfg->helperCore() instanceof Nmmlm_Core_Helper_Data);
        $this->assertTrue($cfg->helperPeriod() instanceof Praxigento_Bonus_Helper_Period);
        $this->assertTrue($cfg->helperType() instanceof Praxigento_Bonus_Helper_Type);
    }

    public function test_service() {
        $cfg = Config::get();
        $this->assertTrue($cfg->service('operations_call') instanceof Praxigento_Bonus_Service_Operations_Call);
    }

    public function test_connectionWrite() {
        $cfg = Config::get();
        $this->assertTrue($cfg->connectionWrite() instanceof Magento_Db_Adapter_Pdo_Mysql);
    }

    public function test_singleton() {
        $cfg = Config::get();
        $this->assertTrue($cfg->singleton('core/resource') instanceof Mage_Core_Model_Resource);
    }
}