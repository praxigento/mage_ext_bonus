<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;
use Praxigento_Bonus_Model_Own_Log_Calc as LogCalc;
use Praxigento_Bonus_Model_Own_Log_Downline as LogDownline;
use Praxigento_Bonus_Model_Own_Period as Period;
use Praxigento_Bonus_Model_Own_Snap_Downline as SnapDownline;
use Praxigento_Bonus_Model_Own_Transaction as Transaction;
use Praxigento_Bonus_Service_Period_Response_GetPeriodForPersonalBonus as GetPeriodForPersonalBonus;
use Praxigento_Bonus_Service_Period_Response_GetPeriodForPvWriteOff as GetPeriodForPvWriteOff;
use Praxigento_Bonus_Service_Snapshot_Response_ChangeUpline as ChangeUplineResponse;

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

    public function test_changeUpline_isTheCustomer() {
        $CUST_ID = 1024;
        $PARENT_ID = 1024;
        /**
         * Create mocks (direct order).
         */
        // $cfg = Config::get();
        $mockCfg = $this
            ->getMockBuilder('Praxigento_Bonus_Config')
            ->setMethods(array( 'singleton', 'model' ))
            ->getMock();
        // $hndlDb = $cfg->singleton(Config::CFG_SERVICE . '/snapshot_hndl_db');
        $mockHndlDb = $this
            ->getMockBuilder('Praxigento_Bonus_Service_Snapshot_Hndl_Db')
            ->setMethods(array( 'getDownlineSnapEntry' ))
            ->getMock();
        $mockCfg
            ->expects($this->at(0))
            ->method('singleton')
            ->will($this->returnValue($mockHndlDb));
        // $result = $cfg->model(Config::CFG_SERVICE . '/snapshot_response_changeUpline');
        $mockCfg
            ->expects($this->at(1))
            ->method('model')
            ->will($this->returnValue(new Praxigento_Bonus_Service_Snapshot_Response_ChangeUpline()));
        /**
         * Setup config and perform call.
         */
        Config::set($mockCfg);
        /** @var  $call Praxigento_Bonus_Service_Snapshot_Call */
        $call = new Praxigento_Bonus_Service_Snapshot_Call();
        /** @var  $req Praxigento_Bonus_Service_Snapshot_Request_ChangeUpline */
        $req = new Praxigento_Bonus_Service_Snapshot_Request_ChangeUpline();
        $req->setCustomerId($CUST_ID);
        $req->setNewUplineId($PARENT_ID);
        $resp = $call->changeUpline($req);
        $this->assertNotNull($resp);
        $this->assertFalse($resp->isSucceed());
        $this->assertEquals(ChangeUplineResponse::ERR_PARENT_IS_THE_CUSTOMER, $resp->getErrorCode());
    }

    public function test_changeUpline_parentAlreadySet() {
        $CUST_ID = 1024;
        $PARENT_NEW_ID = 2048;
        $PARENT_OLD_ID = 2048;
        /**
         * Create mocks (direct order).
         */
        // $cfg = Config::get();
        $mockCfg = $this
            ->getMockBuilder('Praxigento_Bonus_Config')
            ->setMethods(array( 'singleton', 'model' ))
            ->getMock();
        // $hndlDb = $cfg->singleton(Config::CFG_SERVICE . '/snapshot_hndl_db');
        $mockHndlDb = $this
            ->getMockBuilder('Praxigento_Bonus_Service_Snapshot_Hndl_Db')
            ->setMethods(array( 'getDownlineSnapEntry' ))
            ->getMock();
        $mockCfg
            ->expects($this->at(0))
            ->method('singleton')
            ->will($this->returnValue($mockHndlDb));
        // $result = $cfg->model(Config::CFG_SERVICE . '/snapshot_response_changeUpline');
        $mockCfg
            ->expects($this->at(1))
            ->method('model')
            ->will($this->returnValue(new Praxigento_Bonus_Service_Snapshot_Response_ChangeUpline()));
        // $entryCust = $hndlDb->getDownlineSnapEntry($custId);
        $mockEntryCust = new Praxigento_Bonus_Model_Own_Snap_Downline();
        $mockEntryCust->setParentId($PARENT_OLD_ID);
        $mockHndlDb
            ->expects($this->once())
            ->method('getDownlineSnapEntry')
            ->will($this->returnValue($mockEntryCust));
        /**
         * Setup config and perform call.
         */
        Config::set($mockCfg);
        /** @var  $call Praxigento_Bonus_Service_Snapshot_Call */
        $call = new Praxigento_Bonus_Service_Snapshot_Call();
        /** @var  $req Praxigento_Bonus_Service_Snapshot_Request_ChangeUpline */
        $req = new Praxigento_Bonus_Service_Snapshot_Request_ChangeUpline();
        $req->setCustomerId($CUST_ID);
        $req->setNewUplineId($PARENT_NEW_ID);
        $resp = $call->changeUpline($req);
        $this->assertNotNull($resp);
        $this->assertFalse($resp->isSucceed());
        $this->assertEquals(ChangeUplineResponse::ERR_PARENT_ALREADY_SET, $resp->getErrorCode());
    }

    public function test_changeUpline_parentIsInDownline() {
        $CUST_ID = 1024;
        $PARENT_NEW_ID = 4096;
        $PARENT_NEW_PATH = '1/1024/3';
        $PARENT_OLD_ID = 2048;
        /**
         * Create mocks (direct order).
         */
        // $cfg = Config::get();
        $mockCfg = $this
            ->getMockBuilder('Praxigento_Bonus_Config')
            ->setMethods(array( 'singleton', 'model' ))
            ->getMock();
        // $hndlDb = $cfg->singleton(Config::CFG_SERVICE . '/snapshot_hndl_db');
        $mockHndlDb = $this
            ->getMockBuilder('Praxigento_Bonus_Service_Snapshot_Hndl_Db')
            ->setMethods(array( 'getDownlineSnapEntry' ))
            ->getMock();
        $mockCfg
            ->expects($this->at(0))
            ->method('singleton')
            ->will($this->returnValue($mockHndlDb));
        // $result = $cfg->model(Config::CFG_SERVICE . '/snapshot_response_changeUpline');
        $mockCfg
            ->expects($this->at(1))
            ->method('model')
            ->will($this->returnValue(new Praxigento_Bonus_Service_Snapshot_Response_ChangeUpline()));
        // $entryCust = $hndlDb->getDownlineSnapEntry($custId);
        $mockEntryCust = new Praxigento_Bonus_Model_Own_Snap_Downline();
        $mockEntryCust->setParentId($PARENT_OLD_ID);
        $mockHndlDb
            ->expects($this->at(0))
            ->method('getDownlineSnapEntry')
            ->will($this->returnValue($mockEntryCust));
        // $entryNewParent = $hndlDb->getDownlineSnapEntry($newParentId);
        $mockNewParent = new Praxigento_Bonus_Model_Own_Snap_Downline();
        $mockNewParent->setPath($PARENT_NEW_PATH);
        $mockHndlDb
            ->expects($this->at(1))
            ->method('getDownlineSnapEntry')
            ->will($this->returnValue($mockNewParent));
        /**
         * Setup config and perform call.
         */
        Config::set($mockCfg);
        /** @var  $call Praxigento_Bonus_Service_Snapshot_Call */
        $call = new Praxigento_Bonus_Service_Snapshot_Call();
        /** @var  $req Praxigento_Bonus_Service_Snapshot_Request_ChangeUpline */
        $req = new Praxigento_Bonus_Service_Snapshot_Request_ChangeUpline();
        $req->setCustomerId($CUST_ID);
        $req->setNewUplineId($PARENT_NEW_ID);
        $resp = $call->changeUpline($req);
        $this->assertNotNull($resp);
        $this->assertFalse($resp->isSucceed());
        $this->assertEquals(ChangeUplineResponse::ERR_PARENT_IS_FROM_DOWNLINE, $resp->getErrorCode());
    }

    public function test_changeUpline_validated_success() {
        $CUST_ID = 1024;
        $PARENT_NEW_ID = 4096;
        $PARENT_NEW_PATH = '1/2/3';
        $PARENT_OLD_ID = 2048;
        /**
         * Create mocks (direct order).
         */
        // $cfg = Config::get();
        $mockCfg = $this
            ->getMockBuilder('Praxigento_Bonus_Config')
            ->setMethods(array( 'singleton', 'model', 'connectionWrite' ))
            ->getMock();
        // $hndlDb = $cfg->singleton(Config::CFG_SERVICE . '/snapshot_hndl_db');
        $mockHndlDb = $this
            ->getMockBuilder('Praxigento_Bonus_Service_Snapshot_Hndl_Db')
            ->setMethods(array( 'getDownlineSnapEntry', 'updateDownlineSnapParent', 'updateDownlineSnapChildren' ))
            ->getMock();
        $mockCfg
            ->expects($this->at(1))
            ->method('singleton')
            ->will($this->returnValue($mockHndlDb));
        // $result = $cfg->model(Config::CFG_SERVICE . '/snapshot_response_changeUpline');
        $mockCfg
            ->expects($this->at(2))
            ->method('model')
            ->will($this->returnValue(new Praxigento_Bonus_Service_Snapshot_Response_ChangeUpline()));
        // $entryCust = $hndlDb->getDownlineSnapEntry($custId);
        $mockEntryCust = new Praxigento_Bonus_Model_Own_Snap_Downline();
        $mockEntryCust->setParentId($PARENT_OLD_ID);
        $mockHndlDb
            ->expects($this->at(0))
            ->method('getDownlineSnapEntry')
            ->will($this->returnValue($mockEntryCust));
        // $entryNewParent = $hndlDb->getDownlineSnapEntry($newParentId);
        $mockNewParent = new Praxigento_Bonus_Model_Own_Snap_Downline();
        $mockNewParent->setPath($PARENT_NEW_PATH);
        $mockHndlDb
            ->expects($this->at(1))
            ->method('getDownlineSnapEntry')
            ->will($this->returnValue($mockNewParent));
        // $conn = $cfg->connectionWrite();
        $mockConn = $this
            ->getMockBuilder('Varien_Db_Adapter_Pdo_Mysql')
            ->disableOriginalConstructor()
            ->setMethods(array( 'beginTransaction', 'commit', 'rollBack', 'insert' ))
            ->getMock();
        $mockCfg
            ->expects($this->any())
            ->method('connectionWrite')
            ->will($this->returnValue($mockConn));
        /**
         * Setup config and perform call.
         */
        Config::set($mockCfg);
        /** @var  $call Praxigento_Bonus_Service_Snapshot_Call */
        $call = new Praxigento_Bonus_Service_Snapshot_Call();
        /** @var  $req Praxigento_Bonus_Service_Snapshot_Request_ChangeUpline */
        $req = new Praxigento_Bonus_Service_Snapshot_Request_ChangeUpline();
        $req->setCustomerId($CUST_ID);
        $req->setNewUplineId($PARENT_NEW_ID);
        $resp = $call->changeUpline($req);
        $this->assertNotNull($resp);
        $this->assertTrue($resp->isSucceed());
    }

    public function test_changeUpline_validated_exception() {
        $CUST_ID = 1024;
        $PARENT_NEW_ID = 4096;
        $PARENT_NEW_PATH = '1/2/3';
        $PARENT_OLD_ID = 2048;
        /**
         * Create mocks (direct order).
         */
        // $cfg = Config::get();
        $mockCfg = $this
            ->getMockBuilder('Praxigento_Bonus_Config')
            ->setMethods(array( 'singleton', 'model', 'connectionWrite' ))
            ->getMock();
        // $hndlDb = $cfg->singleton(Config::CFG_SERVICE . '/snapshot_hndl_db');
        $mockHndlDb = $this
            ->getMockBuilder('Praxigento_Bonus_Service_Snapshot_Hndl_Db')
            ->setMethods(array( 'getDownlineSnapEntry', 'updateDownlineSnapParent', 'updateDownlineSnapChildren' ))
            ->getMock();
        $mockCfg
            ->expects($this->at(1))
            ->method('singleton')
            ->will($this->returnValue($mockHndlDb));
        // $result = $cfg->model(Config::CFG_SERVICE . '/snapshot_response_changeUpline');
        $mockCfg
            ->expects($this->at(2))
            ->method('model')
            ->will($this->returnValue(new Praxigento_Bonus_Service_Snapshot_Response_ChangeUpline()));
        // $entryCust = $hndlDb->getDownlineSnapEntry($custId);
        $mockEntryCust = new Praxigento_Bonus_Model_Own_Snap_Downline();
        $mockEntryCust->setParentId($PARENT_OLD_ID);
        $mockHndlDb
            ->expects($this->at(0))
            ->method('getDownlineSnapEntry')
            ->will($this->returnValue($mockEntryCust));
        // $entryNewParent = $hndlDb->getDownlineSnapEntry($newParentId);
        $mockNewParent = new Praxigento_Bonus_Model_Own_Snap_Downline();
        $mockNewParent->setPath($PARENT_NEW_PATH);
        $mockHndlDb
            ->expects($this->at(1))
            ->method('getDownlineSnapEntry')
            ->will($this->returnValue($mockNewParent));
        // $conn = $cfg->connectionWrite();
        $mockConn = $this
            ->getMockBuilder('Varien_Db_Adapter_Pdo_Mysql')
            ->disableOriginalConstructor()
            ->setMethods(array( 'beginTransaction', 'commit', 'rollBack', 'insert' ))
            ->getMock();
        $mockCfg
            ->expects($this->any())
            ->method('connectionWrite')
            ->will($this->returnValue($mockConn));
        // $conn->insert($tblLogDwnl, $bind);
        $mockConn
            ->expects($this->any())
            ->method('insert')
            ->will($this->throwException(new Exception));
        /**
         * Setup config and perform call.
         */
        Config::set($mockCfg);
        /** @var  $call Praxigento_Bonus_Service_Snapshot_Call */
        $call = new Praxigento_Bonus_Service_Snapshot_Call();
        /** @var  $req Praxigento_Bonus_Service_Snapshot_Request_ChangeUpline */
        $req = new Praxigento_Bonus_Service_Snapshot_Request_ChangeUpline();
        $req->setCustomerId($CUST_ID);
        $req->setNewUplineId($PARENT_NEW_ID);
        $resp = $call->changeUpline($req);
        $this->assertNotNull($resp);
        $this->assertFalse($resp->isSucceed());
    }

    public function test_getDownlineSnapshotEntry_notFound() {
        $CUST_ID = 1024;
        $PERIOD_VALUE = '201506';
        $PERIOD_EXACT = '20150630';
        // $cfg = Config::get();
        $mockCfg = $this
            ->getMockBuilder('Praxigento_Bonus_Config')
            ->setMethods(array( 'helperPeriod', 'model', 'singleton', 'connectionWrite', 'tableName' ))
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
        // $result = $cfg->model(Config::CFG_SERVICE . '/snapshot_response_getDownlineSnapshotEntry');
        $mockCfg
            ->expects($this->at(3))
            ->method('model')
            ->will($this->returnValue(new Praxigento_Bonus_Service_Snapshot_Response_GetDownlineSnapshotEntry()));
        // $periodExact = $hlpPeriod->calcPeriodSmallest($periodValue);
        $mockHlpPeriod
            ->expects($this->any())
            ->method('calcPeriodSmallest')
            ->will($this->returnValue($PERIOD_EXACT));
        // $conn = $cfg->connectionWrite();
        $mockConn = $this
            ->getMockBuilder('Varien_Db_Adapter_Pdo_Mysql')
            ->disableOriginalConstructor()
            ->setMethods(array( 'fetchAll', 'fetchRow' ))
            ->getMock();
        $mockCfg
            ->expects($this->any())
            ->method('connectionWrite')
            ->will($this->returnValue($mockConn));

        /**
         * Setup config and perform call.
         */
        Config::set($mockCfg);
        /** @var  $call Praxigento_Bonus_Service_Snapshot_Call */
        $call = new Praxigento_Bonus_Service_Snapshot_Call();
        /** @var  $req Praxigento_Bonus_Service_Snapshot_Request_GetDownlineSnapshotEntry */
        $req = new Praxigento_Bonus_Service_Snapshot_Request_GetDownlineSnapshotEntry();
        $req->setCustomerId($CUST_ID);
        $req->setPeriodValue($PERIOD_VALUE);
        $resp = $call->getDownlineSnapshotEntry($req);
        $this->assertNotNull($resp);
        $this->assertFalse($resp->isSucceed());
        $this->assertEquals($resp::ERR_SNAP_IS_NOT_FOUND, $resp->getErrorCode());
    }

    public function test_getDownlineSnapshotEntry_foundByPK() {
        $CUST_ID = 1024;
        $PERIOD_VALUE = '201506';
        $PERIOD_EXACT = '20150630';
        // $cfg = Config::get();
        $mockCfg = $this
            ->getMockBuilder('Praxigento_Bonus_Config')
            ->setMethods(array( 'helperPeriod', 'model', 'singleton', 'connectionWrite', 'tableName' ))
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
        // $result = $cfg->model(Config::CFG_SERVICE . '/snapshot_response_getDownlineSnapshotEntry');
        $mockCfg
            ->expects($this->at(3))
            ->method('model')
            ->will($this->returnValue(new Praxigento_Bonus_Service_Snapshot_Response_GetDownlineSnapshotEntry()));
        // $periodExact = $hlpPeriod->calcPeriodSmallest($periodValue);
        $mockHlpPeriod
            ->expects($this->any())
            ->method('calcPeriodSmallest')
            ->will($this->returnValue($PERIOD_EXACT));
        // $conn = $cfg->connectionWrite();
        $mockConn = $this
            ->getMockBuilder('Varien_Db_Adapter_Pdo_Mysql')
            ->disableOriginalConstructor()
            ->setMethods(array( 'fetchAll', 'fetchRow' ))
            ->getMock();
        $mockCfg
            ->expects($this->any())
            ->method('connectionWrite')
            ->will($this->returnValue($mockConn));
        // $entry = $conn->fetchAll($sql, $bind);
        $mockConn
            ->expects($this->any())
            ->method('fetchAll')
            ->will($this->returnValue(array(
                array(
                    SnapDownline::ATTR_CUSTOMER_ID => 1,
                    SnapDownline::ATTR_DEPTH       => 2,
                    SnapDownline::ATTR_PARENT_ID   => 3,
                    SnapDownline::ATTR_PATH        => '/1/2/3',
                    SnapDownline::ATTR_PERIOD      => $PERIOD_EXACT,
                )
            )));
        /**
         * Setup config and perform call.
         */
        Config::set($mockCfg);
        /** @var  $call Praxigento_Bonus_Service_Snapshot_Call */
        $call = new Praxigento_Bonus_Service_Snapshot_Call();
        /** @var  $req Praxigento_Bonus_Service_Snapshot_Request_GetDownlineSnapshotEntry */
        $req = new Praxigento_Bonus_Service_Snapshot_Request_GetDownlineSnapshotEntry();
        $req->setCustomerId($CUST_ID);
        $req->setPeriodValue($PERIOD_VALUE);
        $resp = $call->getDownlineSnapshotEntry($req);
        $this->assertNotNull($resp);
        $this->assertTrue($resp->isSucceed());
        $this->assertEquals($PERIOD_EXACT, $resp->getPeriodExact());
    }
}