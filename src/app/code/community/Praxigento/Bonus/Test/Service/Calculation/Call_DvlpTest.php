<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;
use Praxigento_Bonus_Model_Own_Balance as Balance;

include_once('../../phpunit_bootstrap.php');

/**
 * Development tests are the test environment to perform real operations with DB data.
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Test_Service_Calculation_Call_DvlpTest
    extends PHPUnit_Framework_TestCase {

    public function test_constructor() {
        /** @var  $call Praxigento_Bonus_Service_Calculation_Call */
        $call = Config::get()->serviceCalculation();
        $this->assertNotNull($call);
        $this->assertTrue($call instanceof Praxigento_Bonus_Service_Calculation_Call);
    }

    public function test_getOperationsForPvWriteOff() {
        /** @var  $call Praxigento_Bonus_Service_Calculation_Call */
        $call = Config::get()->serviceCalculation();
        $resp = $call->calcPvWriteOff();
        $this->assertTrue($resp instanceof Praxigento_Bonus_Service_Calculation_Response_CalcPvWriteOff);
    }

}