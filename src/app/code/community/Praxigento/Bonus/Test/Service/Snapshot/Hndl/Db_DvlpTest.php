<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;
use Praxigento_Bonus_Model_Own_Log_Calc as LogCalc;
use Praxigento_Bonus_Service_Operations_Response_GetOperationsForPvWriteOff as GetOperationsForPvWriteOffResponse;
use Praxigento_Bonus_Service_Period_Response_GetPeriodForPvWriteOff as GetPeriodForPvWriteOffResponse;

include_once('../../../phpunit_bootstrap.php');

/**
 * Development environment is not participated in the automatic test.
 *
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Test_Service_Snapshot_Hndl_Db_DvlpTest
    extends PHPUnit_Framework_TestCase {

    /**
     * Reset Config before each test.
     */
    public function setUp() {
        Config::set(null);
    }

    public function test_constructor() {
        /** @var  $hndl Praxigento_Bonus_Service_Snapshot_Hndl_Db */
        $hndl = new Praxigento_Bonus_Service_Snapshot_Hndl_Db();
        $this->assertNotNull($hndl);
    }

    public function test_getDownlineSnapEntry() {
        $CUST_ID = '3';
        /** @var  $hndl Praxigento_Bonus_Service_Snapshot_Hndl_Db */
        $hndl = new Praxigento_Bonus_Service_Snapshot_Hndl_Db();
        $data = $hndl->getDownlineSnapEntry($CUST_ID);
        $this->assertNotNull($data);
    }

    public function test_getDownlineSnapForPeriod() {
        /** @var  $hndl Praxigento_Bonus_Service_Snapshot_Hndl_Db */
        $hndl = new Praxigento_Bonus_Service_Snapshot_Hndl_Db();
        $data = $hndl->getDownlineSnapForPeriod(Config::PERIOD_KEY_NOW);
        $this->assertNotNull($data);
    }

    public function test_updateDownlineSnapChildren() {
        /** @var  $hndl Praxigento_Bonus_Service_Snapshot_Hndl_Db */
        $hndl = new Praxigento_Bonus_Service_Snapshot_Hndl_Db();
        $result = $hndl->updateDownlineSnapChildren('/some/old/path/', '/some/new/path/', 2);
        $this->assertNull($result);
    }

}