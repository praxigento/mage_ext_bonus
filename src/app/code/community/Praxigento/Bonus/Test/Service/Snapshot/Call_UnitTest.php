<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;
use Praxigento_Bonus_Model_Own_Log_Calc as LogCalc;
use Praxigento_Bonus_Model_Own_Period as Period;
use Praxigento_Bonus_Model_Own_Transaction as Transaction;
use Praxigento_Bonus_Service_Period_Response_GetPeriodForPersonalBonus as GetPeriodForPersonalBonus;
use Praxigento_Bonus_Service_Period_Response_GetPeriodForPvWriteOff as GetPeriodForPvWriteOff;

include_once('../../phpunit_bootstrap.php');

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Test_Service_Snapshot_Call_UnitTest
    extends PHPUnit_Framework_TestCase {


    /**
     * Reset Config before each test.
     */
    public function setUp() {
        Config::set(null);
    }

    public function test_constructor() {
        /** @var  $call Praxigento_Bonus_Service_Snapshot_Call */
        $call = Config::get()->serviceSnapshot();
        $this->assertNotNull($call);
    }

    public function test_requests() {
        /** @var  $call Praxigento_Bonus_Service_Snapshot_Call */
        $call = Config::get()->serviceSnapshot();
        $req = $call->requestChangeUpline();
        $this->assertTrue($req instanceof Praxigento_Bonus_Service_Snapshot_Request_ChangeUpline);
        $req = $call->requestComposeDownlineSnapshot();
        $this->assertTrue($req instanceof Praxigento_Bonus_Service_Snapshot_Request_ComposeDownlineSnapshot);
        $req = $call->requestGetDownlineSnapshotEntry();
        $this->assertTrue($req instanceof Praxigento_Bonus_Service_Snapshot_Request_GetDownlineSnapshotEntry);
        $req = $call->requestValidateDownlineSnapshot();
        $this->assertTrue($req instanceof Praxigento_Bonus_Service_Snapshot_Request_ValidateDownlineSnapshot);
    }

    public function test_composeDownlineSnapshot_periodExists() {
        $PERIOD_VALUE = '201506';
        $PERIOD_VALUE_DAY = '20150630';
        /**
         * Create mocks (direct order).
         */
        // $cfg = Config::get();
        $mockCfg = $this
            ->getMockBuilder('Praxigento_Bonus_Config')
            ->setMethods(array( 'helperPeriod', 'singleton', 'model' ))
            ->getMock();
        // $hlpPeriod = $cfg->helperPeriod();
        $mockHlpPeriod = $this
            ->getMockBuilder('Praxigento_Bonus_Helper_Period')
            ->setMethods(array( 'calcPeriodSmallest' ))
            ->getMock();
        $mockCfg
            ->expects($this->any())
            ->method('helperPeriod')
            ->will($this->returnValue($mockHlpPeriod));
        // $hndlDb = $cfg->singleton(Config::CFG_SERVICE . '/snapshot_hndl_db');
        $mockHndlDb = $this
            ->getMockBuilder('Praxigento_Bonus_Service_Snapshot_Hndl_Db')
            ->setMethods(array( 'isThereDownlinesSnapForPeriod' ))
            ->getMock();
        $mockCfg
            ->expects($this->at(2))
            ->method('singleton')
            ->will($this->returnValue($mockHndlDb));
        // $hndlDownline = $cfg->singleton(Config::CFG_SERVICE . '/snapshot_hndl_downline');
        $mockHndlDownline = $this
            ->getMockBuilder('Praxigento_Bonus_Service_Snapshot_Hndl_Downline')
            ->getMock();
        $mockCfg
            ->expects($this->at(3))
            ->method('singleton')
            ->will($this->returnValue($mockHndlDownline));
        // $result = $cfg->model(Config::CFG_SERVICE . '/snapshot_response_composeDownlineSnapshot');
        $mockCfg
            ->expects($this->at(4))
            ->method('model')
            //            ->with($this->equalTo(Config::CFG_SERVICE . '/snapshot_response_composeDownlineSnapshot'))
            ->will($this->returnValue(new Praxigento_Bonus_Service_Snapshot_Response_ComposeDownlineSnapshot()));
        // $periodValueDaily = $hlpPeriod->calcPeriodSmallest($periodValue);
        $mockHlpPeriod
            ->expects($this->once())
            ->method('calcPeriodSmallest')
            ->with($this->equalTo($PERIOD_VALUE))
            ->will($this->returnValue($PERIOD_VALUE_DAY));
        // $periodExists = $hndlDb->isThereDownlinesSnapForPeriod($periodValueDaily);
        $mockHndlDb
            ->expects($this->once())
            ->method('isThereDownlinesSnapForPeriod')
            ->with($this->equalTo($PERIOD_VALUE_DAY))
            ->will($this->returnValue($PERIOD_VALUE_DAY));
        /**
         * Setup config and perform call.
         */
        Config::set($mockCfg);
        /** @var  $call Praxigento_Bonus_Service_Snapshot_Call */
        $call = Config::get()->serviceSnapshot();
        /** @var  $req Praxigento_Bonus_Service_Snapshot_Request_ComposeDownlineSnapshot */
        $req = new Praxigento_Bonus_Service_Snapshot_Request_ComposeDownlineSnapshot();
        $req->setPeriodValue($PERIOD_VALUE);
        $resp = $call->composeDownlineSnapshot($req);
        $this->assertNotNull($resp);
        $this->assertTrue($resp->isSucceed());
        $this->assertEquals($PERIOD_VALUE_DAY, $resp->getPeriodValue());
    }

    public function skp_test_composeDownlineSnapshot_createSnapshot() {
        $PERIOD_VALUE = '20150601';
        /**
         * Create mocks (direct order).
         */
        /* Config:: */
        $mockCfg = $this
            ->getMockBuilder('Praxigento_Bonus_Config')
            ->setMethods(array( 'model' ))
            ->getMock();
        $mockCfg
            ->expects($this->at(2))
            ->method('model')
            ->with($this->equalTo(Config::CFG_SERVICE . '/snapshot_request_composeDownlineSnapshot'))
            ->will($this->returnValue(new Praxigento_Bonus_Service_Snapshot_Request_ComposeDownlineSnapshot()));
        $mockCfg
            ->expects($this->at(3))
            ->method('model')
            ->with($this->equalTo(Config::CFG_SERVICE . '/snapshot_response_composeDownlineSnapshot'))
            ->will($this->returnValue(new Praxigento_Bonus_Service_Snapshot_Response_ComposeDownlineSnapshot()));
        // $this->_hndlDb = Config::get()->model(Config::CFG_SERVICE . '/snapshot_hndl_db');
        $mockThisHndlDb = $this
            ->getMockBuilder('Praxigento_Bonus_Service_Snapshot_Hndl_Db')
            ->setMethods(array( 'isThereDownlinesSnapForPeriod' ))
            ->getMock();
        $mockCfg
            ->expects($this->at(0))
            ->method('model')
            ->with($this->equalTo(Config::CFG_SERVICE . '/snapshot_hndl_db'))
            ->will($this->returnValue($mockThisHndlDb));
        // $periodExists = $this->_hndlDb->isThereDownlinesSnapForPeriod($periodValue);
        $mockThisHndlDb
            ->expects($this->once())
            ->method('isThereDownlinesSnapForPeriod')
            ->with($this->equalTo($PERIOD_VALUE))
            ->will($this->returnValue($PERIOD_VALUE));
        /**
         * Setup config and perform call.
         */
        Config::set($mockCfg);
        /** @var  $call Praxigento_Bonus_Service_Snapshot_Call */
        $call = Config::get()->serviceSnapshot();
        /** @var  $req Praxigento_Bonus_Service_Snapshot_Request_ComposeDownlineSnapshot */
        $req = $call->requestComposeDownlineSnapshot();
        $req->setPeriodValue($PERIOD_VALUE);
        $resp = $call->composeDownlineSnapshot($req);
        $this->assertNotNull($resp);
        $this->assertTrue($resp->isSucceed());
        $this->assertEquals($PERIOD_VALUE, $resp->getPeriodValue());
    }


}