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
class Praxigento_Bonus_Test_Service_Snapshot_Call_DvlpTest
    extends PHPUnit_Framework_TestCase {

    public function test_constructor() {
        /** @var  $call Praxigento_Bonus_Service_Snapshot_Call */
        $call = Config::get()->serviceOperations();
        $this->assertNotNull($call);
        $this->assertTrue($call instanceof Praxigento_Bonus_Service_Snapshot_Call);
    }

    public function test_composeDownlineSnapshot() {
        $PERIOD = '211506';
        /** @var  $call Praxigento_Bonus_Service_Snapshot_Call */
        $call = Config::get()->serviceSnapshot();
        /** @var  $req Praxigento_Bonus_Service_Snapshot_Request_ComposeDownlineSnapshot */
        $req = $call->requestComposeDownlineSnapshot();
        $req->setPeriodValue($PERIOD);
        $resp = $call->composeDownlineSnapshot($req);
        $this->assertTrue($resp instanceof Praxigento_Bonus_Service_Snapshot_Response_ComposeDownlineSnapshot);
    }

}