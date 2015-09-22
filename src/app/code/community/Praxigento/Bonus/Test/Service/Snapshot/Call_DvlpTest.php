<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;
use Praxigento_Bonus_Model_Own_Balance as Balance;
use Praxigento_Bonus_Service_Snapshot_Request_ChangeUpline as ChangeUplineRequest;

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
        $PERIOD = '201509';
        /** @var  $call Praxigento_Bonus_Service_Snapshot_Call */
        $call = Config::get()->serviceSnapshot();
        /** @var  $req Praxigento_Bonus_Service_Snapshot_Request_ComposeDownlineSnapshot */
        $req = $call->requestComposeDownlineSnapshot();
        $req->setPeriodValue($PERIOD);
        $resp = $call->composeDownlineSnapshot($req);
        $this->assertTrue($resp instanceof Praxigento_Bonus_Service_Snapshot_Response_ComposeDownlineSnapshot);
    }

    public function test_getDownlineSnapshotEntry() {
        $PERIOD = '201505';
        $CUST_ID = '2506';
        /** @var  $call Praxigento_Bonus_Service_Snapshot_Call */
        $call = Config::get()->serviceSnapshot();
        /** @var  $req Praxigento_Bonus_Service_Snapshot_Request_GetDownlineSnapshotEntry */
        $req = $call->requestGetDownlineSnapshotEntry();
        $req->setCustomerId($CUST_ID);
        $req->setPeriodValue($PERIOD);
        $resp = $call->getDownlineSnapshotEntry($req);
        $this->assertTrue($resp instanceof Praxigento_Bonus_Service_Snapshot_Response_GetDownlineSnapshotEntry);
    }

    public function test_changeUpline() {
        $CUST_ID = '846';
        $UPLINE_ID = '909';
        /** @var  $call Praxigento_Bonus_Service_Snapshot_Call */
        $call = Config::get()->serviceSnapshot();
        /** @var  $req ChangeUplineRequest */
        $req = $call->requestChangeUpline();
        $req->setCustomerId($CUST_ID);
        $req->setNewUplineId($UPLINE_ID);
        $resp = $call->changeUpline($req);
        $this->assertTrue($resp instanceof Praxigento_Bonus_Service_Snapshot_Response_ChangeUpline);
    }

    public function test_validateDownlineSnapshot() {
        $PERIOD = 'NOW';
        /** @var  $call Praxigento_Bonus_Service_Snapshot_Call */
        $call = Config::get()->serviceSnapshot();
        /** @var  $req Praxigento_Bonus_Service_Snapshot_Request_ValidateDownlineSnapshot */
        $req = $call->requestValidateDownlineSnapshot();
        $req->setPeriodValue($PERIOD);
        $resp = $call->validateDownlineSnapshot($req);
        $this->assertTrue($resp instanceof Praxigento_Bonus_Service_Snapshot_Response_ValidateDownlineSnapshot);
        $this->assertTrue(is_array($resp->getAllOrphans()));
        $this->assertTrue(is_array($resp->getAllWrongPaths()));
        $this->assertTrue(is_int($resp->getMaxDepth()));
        $this->assertTrue(is_int($resp->getTotalCustomers()));
        $this->assertTrue(is_int($resp->getTotalOrphans()));
        $this->assertTrue(is_int($resp->getTotalRoots()));
        $this->assertTrue(is_int($resp->getTotalWrongPaths()));
    }

}