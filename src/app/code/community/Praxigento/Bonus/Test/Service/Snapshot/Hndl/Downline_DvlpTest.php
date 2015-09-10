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
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Test_Service_Snapshot_Hndl_Downline_DvlpTest
    extends PHPUnit_Framework_TestCase {

    /**
     * Reset Config before each test.
     */
    public function setUp() {
        Config::set(null);
    }

    public function test_constructor() {
        /**
         * Create mocks (direct order).
         */

        /** @var  $hndl Praxigento_Bonus_Service_Snapshot_Hndl_Downline */
        $hndl = new Praxigento_Bonus_Service_Snapshot_Hndl_Downline();
        $this->assertNotNull($hndl);
    }

    public function test_transformIdsToSnapItems() {
        /**
         * Create input data.
         */
        $arrData = array();
        $arrData[1] = 1;
        for($i = 2; $i < 1000; $i++) {
            $rand = rand(1, ceil(($i - 1) / 4));
            $arrData[ $i ] = $rand;
        }

        /** @var  $hndl Praxigento_Bonus_Service_Snapshot_Hndl_Downline */
        $hndl = new Praxigento_Bonus_Service_Snapshot_Hndl_Downline();

        $snapshots = $hndl->transformIdsToSnapItems($arrData, '20150910');
        1 + 1;
    }

}