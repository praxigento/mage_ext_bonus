<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;
use Praxigento_Bonus_Model_Own_Log_Calc as LogCalc;
use Praxigento_Bonus_Model_Own_Snap_Downline as SnapDownline;
use Praxigento_Bonus_Service_Operations_Response_GetOperationsForPvWriteOff as GetOperationsForPvWriteOffResponse;
use Praxigento_Bonus_Service_Period_Response_GetPeriodForPvWriteOff as GetPeriodForPvWriteOffResponse;

include_once('../../../phpunit_bootstrap.php');

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Test_Service_Snapshot_Hndl_Downline_UnitTest
    extends PHPUnit_Framework_TestCase {

    /**
     * Reset Config before each test.
     */
    public function setUp() {
        Config::set(null);
    }

    public function test_constructor() {
        /** @var  $hndl Praxigento_Bonus_Service_Snapshot_Hndl_Downline */
        $hndl = new Praxigento_Bonus_Service_Snapshot_Hndl_Downline();
        $this->assertNotNull($hndl);
    }

    public function test_transformIdsToSnapItems() {
        $ARR_CUST_PARENT = array(
            1 => 1,
            2 => 2,
            3 => 1
        );
        $PERIOD = Config::PERIOD_KEY_NOW;
        /**
         * Create mocks (direct order).
         */

        /**
         * Setup config and perform call.
         */
        /** @var  $hndl Praxigento_Bonus_Service_Snapshot_Hndl_Downline */
        $hndl = new Praxigento_Bonus_Service_Snapshot_Hndl_Downline();
        $result = $hndl->transformIdsToSnapItems($ARR_CUST_PARENT, $PERIOD);
        $this->assertNotNull($result);
        $this->assertTrue(is_array($result));
        $this->assertTrue(is_array($result[1]));
        $entry = $result[1];
        $this->assertEquals(1, $entry[ SnapDownline::ATTR_CUSTOMER_ID ]);
        $this->assertEquals(1, $entry[ SnapDownline::ATTR_PARENT_ID ]);
        $this->assertEquals($PERIOD, $entry[ SnapDownline::ATTR_PERIOD ]);
        $this->assertEquals(Config::MPS, $entry[ SnapDownline::ATTR_PATH ]);
        $this->assertEquals(1, $entry[ SnapDownline::ATTR_DEPTH ]);

    }

}