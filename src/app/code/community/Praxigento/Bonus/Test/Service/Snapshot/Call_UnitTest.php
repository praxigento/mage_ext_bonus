<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;
use Praxigento_Bonus_Model_Own_Log_Calc as LogCalc;
use Praxigento_Bonus_Model_Own_Log_Downline as LogDownline;
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

    public function test_composeDownlineSnapshot_noPeriod_noSnap_success() {
        $PERIOD_VALUE = '201506';
        $PERIOD_VALUE_DAY = '20150630';
        $FROM_TS = '2015-06-01 07:00:00';
        $TO_TS = '2015-07-01 06:59:59';
        $CUST_ID = 1;
        $PARENT_ID = 1;
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
            ->setMethods(array( 'calcPeriodSmallest', 'calcPeriodTsTo', 'calcPeriodTsNextFrom' ))
            ->getMock();
        $mockCfg
            ->expects($this->any())
            ->method('helperPeriod')
            ->will($this->returnValue($mockHlpPeriod));
        // $hndlDb = $cfg->singleton(Config::CFG_SERVICE . '/snapshot_hndl_db');
        $mockHndlDb = $this
            ->getMockBuilder('Praxigento_Bonus_Service_Snapshot_Hndl_Db')
            ->setMethods(array(
                'isThereDownlinesSnapForPeriod',
                'getLatestDownlineSnapBeforePeriod',
                'getFirstDownlineLogBeforePeriod',
                'getDownlineLogs',
                'saveDownlineSnaps'
            ))
            ->getMock();
        $mockCfg
            ->expects($this->at(2))
            ->method('singleton')
            ->will($this->returnValue($mockHndlDb));
        // $hndlDownline = $cfg->singleton(Config::CFG_SERVICE . '/snapshot_hndl_downline');
        $mockHndlDownline = $this
            ->getMockBuilder('Praxigento_Bonus_Service_Snapshot_Hndl_Downline')
            ->setMethods(array( 'transformIdsToSnapItems' ))
            ->getMock();
        $mockCfg
            ->expects($this->at(3))
            ->method('singleton')
            ->will($this->returnValue($mockHndlDownline));
        // $result = $cfg->model(Config::CFG_SERVICE . '/snapshot_response_composeDownlineSnapshot');
        $mockCfg
            ->expects($this->at(4))
            ->method('model')
            ->will($this->returnValue(new Praxigento_Bonus_Service_Snapshot_Response_ComposeDownlineSnapshot()));
        // $periodValueDaily = $hlpPeriod->calcPeriodSmallest($periodValue);
        $mockHlpPeriod
            ->expects($this->any())
            ->method('calcPeriodSmallest')
            ->will($this->returnValue($PERIOD_VALUE_DAY));
        // $periodExists = $hndlDb->isThereDownlinesSnapForPeriod($periodValueDaily);
        $mockHndlDb
            ->expects($this->once())
            ->method('isThereDownlinesSnapForPeriod')
            ->with($this->equalTo($PERIOD_VALUE_DAY))
            ->will($this->returnValue(null));
        // $to = $hlpPeriod->calcPeriodTsTo($periodValueDaily, Config::PERIOD_DAY);
        $mockHlpPeriod
            ->expects($this->any())
            ->method('calcPeriodTsTo')
            ->will($this->returnValue($TO_TS));
        // $maxExistingPeriod = $hndlDb->getLatestDownlineSnapBeforePeriod($periodValueDaily);
        $mockHndlDb
            ->expects($this->any())
            ->method('getLatestDownlineSnapBeforePeriod')
            ->with($this->equalTo($PERIOD_VALUE_DAY))
            ->will($this->returnValue(null));
        // $from = $hndlDb->getFirstDownlineLogBeforePeriod($periodValue);
        $mockHndlDb
            ->expects($this->once())
            ->method('getFirstDownlineLogBeforePeriod')
            ->will($this->returnValue($FROM_TS));
        // $logs = $hndlDb->getDownlineLogs($from, $to);
        $mockHndlDb
            ->expects($this->once())
            ->method('getDownlineLogs')
            ->with(
                $this->equalTo($FROM_TS),
                $this->equalTo($TO_TS)
            )
            ->will($this->returnValue(array(
                array(
                    LogDownline::ATTR_CUSTOMER_ID => $CUST_ID,
                    LogDownline::ATTR_PARENT_ID   => $PARENT_ID
                )
            )));
        // $snapshot = $hndlDownline->transformIdsToSnapItems($arrAggregated, $periodValueDaily);
        $mockHndlDownline
            ->expects($this->once())
            ->method('transformIdsToSnapItems')
            ->will($this->returnValue(null));
        // $hndlDb->saveDownlineSnaps($snapshot);
        $mockHndlDb
            ->expects($this->once())
            ->method('saveDownlineSnaps')
            ->will($this->returnValue(null));
        /**
         * Setup config and perform call.
         */
        Config::set($mockCfg);
        /** @var  $call Praxigento_Bonus_Service_Snapshot_Call */
        $call = new Praxigento_Bonus_Service_Snapshot_Call();
        /** @var  $req Praxigento_Bonus_Service_Snapshot_Request_ComposeDownlineSnapshot */
        $req = new Praxigento_Bonus_Service_Snapshot_Request_ComposeDownlineSnapshot();
        $req->setPeriodValue($PERIOD_VALUE);
        $resp = $call->composeDownlineSnapshot($req);
        $this->assertNotNull($resp);
        $this->assertTrue($resp->isSucceed());
        $this->assertEquals($PERIOD_VALUE, $resp->getPeriodValue());
    }

    public function test_composeDownlineSnapshot_noPeriod_isSnap_exception() {
        $PERIOD_VALUE = '201506';
        $PERIOD_VALUE_DAY = '20150630';
        $MAX_PERIOD_EXIST = '20150601';
        $FROM_TS = '2015-06-01 07:00:00';
        $TO_TS = '2015-07-01 06:59:59';
        $CUST_ID = 1;
        $PARENT_ID = 1;
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
            ->setMethods(array( 'calcPeriodSmallest', 'calcPeriodTsTo', 'calcPeriodTsNextFrom' ))
            ->getMock();
        $mockCfg
            ->expects($this->any())
            ->method('helperPeriod')
            ->will($this->returnValue($mockHlpPeriod));
        // $hndlDb = $cfg->singleton(Config::CFG_SERVICE . '/snapshot_hndl_db');
        $mockHndlDb = $this
            ->getMockBuilder('Praxigento_Bonus_Service_Snapshot_Hndl_Db')
            ->setMethods(array(
                'isThereDownlinesSnapForPeriod',
                'getLatestDownlineSnapBeforePeriod',
                'getFirstDownlineLogBeforePeriod',
                'getDownlineLogs',
                'getDownlineSnapForPeriod',
                'saveDownlineSnaps'
            ))
            ->getMock();
        $mockCfg
            ->expects($this->at(2))
            ->method('singleton')
            ->will($this->returnValue($mockHndlDb));
        // $hndlDownline = $cfg->singleton(Config::CFG_SERVICE . '/snapshot_hndl_downline');
        $mockHndlDownline = $this
            ->getMockBuilder('Praxigento_Bonus_Service_Snapshot_Hndl_Downline')
            ->setMethods(array( 'transformIdsToSnapItems' ))
            ->getMock();
        $mockCfg
            ->expects($this->at(3))
            ->method('singleton')
            ->will($this->returnValue($mockHndlDownline));
        // $result = $cfg->model(Config::CFG_SERVICE . '/snapshot_response_composeDownlineSnapshot');
        $mockCfg
            ->expects($this->at(4))
            ->method('model')
            ->will($this->returnValue(new Praxigento_Bonus_Service_Snapshot_Response_ComposeDownlineSnapshot()));
        // $periodValueDaily = $hlpPeriod->calcPeriodSmallest($periodValue);
        $mockHlpPeriod
            ->expects($this->any())
            ->method('calcPeriodSmallest')
            ->will($this->returnValue($PERIOD_VALUE_DAY));
        // $periodExists = $hndlDb->isThereDownlinesSnapForPeriod($periodValueDaily);
        $mockHndlDb
            ->expects($this->once())
            ->method('isThereDownlinesSnapForPeriod')
            ->with($this->equalTo($PERIOD_VALUE_DAY))
            ->will($this->returnValue(null));
        // $to = $hlpPeriod->calcPeriodTsTo($periodValueDaily, Config::PERIOD_DAY);
        $mockHlpPeriod
            ->expects($this->any())
            ->method('calcPeriodTsTo')
            ->will($this->returnValue($TO_TS));
        // $maxExistingPeriod = $hndlDb->getLatestDownlineSnapBeforePeriod($periodValueDaily);
        $mockHndlDb
            ->expects($this->any())
            ->method('getLatestDownlineSnapBeforePeriod')
            ->with($this->equalTo($PERIOD_VALUE_DAY))
            ->will($this->returnValue($MAX_PERIOD_EXIST));
        // $latestSnap = $hndlDb->getDownlineSnapForPeriod($maxExistingPeriod);
        $mockHndlDb
            ->expects($this->any())
            ->method('getDownlineSnapForPeriod')
            ->with($this->equalTo($MAX_PERIOD_EXIST))
            ->will($this->returnValue(array(
                LogDownline::ATTR_CUSTOMER_ID => $CUST_ID,
                LogDownline::ATTR_PARENT_ID   => $PARENT_ID
            )));
        // $from = $hlpPeriod->calcPeriodTsNextFrom($maxExistingPeriod, Config::PERIOD_DAY);
        $mockHlpPeriod
            ->expects($this->any())
            ->method('calcPeriodTsNextFrom')
            ->will($this->returnValue($FROM_TS));
        // $logs = $hndlDb->getDownlineLogs($from, $to);
        $mockHndlDb
            ->expects($this->once())
            ->method('getDownlineLogs')
            ->with(
                $this->equalTo($FROM_TS),
                $this->equalTo($TO_TS)
            )
            ->will($this->returnValue(array(
                array(
                    LogDownline::ATTR_CUSTOMER_ID => $CUST_ID,
                    LogDownline::ATTR_PARENT_ID   => $PARENT_ID
                )
            )));
        // $snapshot = $hndlDownline->transformIdsToSnapItems($arrAggregated, $periodValueDaily);
        $mockHndlDownline
            ->expects($this->once())
            ->method('transformIdsToSnapItems')
            ->will($this->returnValue(null));
        // $hndlDb->saveDownlineSnaps($snapshot);
        $mockHndlDb
            ->expects($this->once())
            ->method('saveDownlineSnaps')
            ->will($this->throwException(new Exception));
        /**
         * Setup config and perform call.
         */
        Config::set($mockCfg);
        /** @var  $call Praxigento_Bonus_Service_Snapshot_Call */
        $call = new Praxigento_Bonus_Service_Snapshot_Call();
        /** @var  $req Praxigento_Bonus_Service_Snapshot_Request_ComposeDownlineSnapshot */
        $req = new Praxigento_Bonus_Service_Snapshot_Request_ComposeDownlineSnapshot();
        $req->setPeriodValue($PERIOD_VALUE);
        $resp = $call->composeDownlineSnapshot($req);
        $this->assertNotNull($resp);
        $this->assertFalse($resp->isSucceed());
    }


}