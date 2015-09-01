<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;

include_once('../../phpunit_bootstrap.php');

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Test_Service_Calculation_Call_UnitTest
    extends PHPUnit_Framework_TestCase {
    public function setUp() {
        Config::set(null);
    }

    public function test_constructor() {
        /** @var  $call Praxigento_Bonus_Service_Calculation_Call */
        $call = Config::get()->serviceCalculation();
        $this->assertNotNull($call);
    }

    public function test_getOperationsForPvWriteOff() {
        /** @var  $call Praxigento_Bonus_Service_Calculation_Call */
        $call = Config::get()->serviceCalculation();
        /** @var  $resp Praxigento_Bonus_Service_Calculation_Request_CalcPvWriteOff */
        $req = $call->requestCalcPvWriteOff();
        /** @var  $resp Praxigento_Bonus_Service_Calculation_Response_CalcPvWriteOff */
        $resp = $call->calcPvWriteOff($req);
        $this->assertTrue($resp->isSucceed());
    }

}