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
class Praxigento_Bonus_Test_Service_Period_Call_UnitTest extends PHPUnit_Framework_TestCase {

    public function test_constructor() {
        /** @var  $call Praxigento_Bonus_Service_Period_Call */
        $call = Config::get()->servicePeriod();
        $this->assertNotNull($call);
    }

    public function test_requests() {
        /** @var  $call Praxigento_Bonus_Service_Period_Call */
        $call = Config::get()->servicePeriod();
        $req  = $call->requestPeriodForPersonalBonus();
        $this->assertTrue($req instanceof Praxigento_Bonus_Service_Period_Request_GetPeriodForPersonalBonus);
        $req = $call->requestPeriodForPvWriteOff();
        $this->assertTrue($req instanceof Praxigento_Bonus_Service_Period_Request_GetPeriodForPvWriteOff);
        $req = $call->requestRegisterPeriodCalculation();
        $this->assertTrue($req instanceof Praxigento_Bonus_Service_Period_Request_RegisterPeriodCalculation);
    }


    public function test_registerPeriodCalculation_isPeriod_isLog() {
        $PERIOD_ID = 21;
        $LOG_ID    = 34;
        /**
         * Create mocks.
         */
        /* Period Model */
        $mockPeriod = $this
            ->getMockBuilder('Praxigento_Bonus_Model_Own_Period')
            ->setMethods(array( 'load' ))
            ->getMock();
        $mockPeriod
            ->expects($this->once())
            ->method('load')
            ->with($this->equalTo($PERIOD_ID));
        $mockPeriod->setId($PERIOD_ID);
        /* Log Calc Model */
        $mockLogCalc = $this
            ->getMockBuilder('Praxigento_Bonus_Model_Own_Log_Calc')
            ->setMethods(array( 'load' ))
            ->getMock();
        $mockLogCalc
            ->expects($this->once())
            ->method('load')
            ->with($this->equalTo($LOG_ID));
        $mockLogCalc->setId($LOG_ID);
        /* config */
        $mockCfg = $this
            ->getMockBuilder('Praxigento_Bonus_Config')
            ->setMethods(array( 'modelPeriod', 'modelLogCalc' ))
            ->getMock();
        $mockCfg
            ->expects($this->any())
            ->method('modelPeriod')
            ->will($this->returnValue($mockPeriod));
        $mockCfg
            ->expects($this->any())
            ->method('modelLogCalc')
            ->will($this->returnValue($mockLogCalc));
        /* setup Config */
        Config::set($mockCfg);
        /** @var  $call Praxigento_Bonus_Service_Period_Call */
        $call = Config::get()->servicePeriod();
        $req  = $call->requestRegisterPeriodCalculation();
        $req->setPeriodId($PERIOD_ID);
        $req->setLogCalcId($LOG_ID);
        $resp = $call->registerPeriodCalculation($req);
        $this->assertEquals($PERIOD_ID, $resp->getPeriod()->getId());
        $this->assertEquals($LOG_ID, $resp->getLogCalc()->getId());
    }

    public function test_registerPeriodCalculation_foundPeriod_isLog() {
        $PERIOD_VAL      = '20150601';
        $CALC_TYPE_ID    = 21;
        $PERIOD_TYPE_ID  = 54;
        $FOUND_PERIOD_ID = 999;
        $FOUND_CALC_ID   = 888;
        /**
         * Create mocks.
         */
        /* found period */
        $mockPeriod = $this
            ->getMockBuilder('Praxigento_Bonus_Model_Own_Period')
            ->setMethods(array( 'getId' ))
            ->getMock();
        $mockPeriod
            ->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($FOUND_PERIOD_ID));
        /* periods collection */
        $mockCollPeriod = $this
            ->getMockBuilder('Praxigento_Bonus_Resource_Own_Period_Collection')
            ->setMethods(array( 'getSize', 'getFirstItem' ))
            ->getMock();
        $mockCollPeriod
            ->expects($this->once())
            ->method('getSize')
            ->will($this->returnValue(1));
        $mockCollPeriod
            ->expects($this->once())
            ->method('getFirstItem')
            ->will($this->returnValue($mockPeriod));
        /* found calculation */
        $mockCalc = $this
            ->getMockBuilder('Praxigento_Bonus_Model_Own_Log_Calc')
            ->setMethods(array( 'getId' ))
            ->getMock();
        $mockCalc
            ->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($FOUND_CALC_ID));
        /* calculations collection */
        $mockCollCalc = $this
            ->getMockBuilder('Praxigento_Bonus_Resource_Own_Log_Calc_Collection')
            ->setMethods(array( 'getSize', 'getFirstItem' ))
            ->getMock();
        $mockCollCalc
            ->expects($this->once())
            ->method('getSize')
            ->will($this->returnValue(1));
        $mockCollCalc
            ->expects($this->once())
            ->method('getFirstItem')
            ->will($this->returnValue($mockCalc));
        /* config */
        $mockCfg = $this
            ->getMockBuilder('Praxigento_Bonus_Config')
            ->setMethods(array( 'collectionPeriod', 'collectionLogCalc' ))
            ->getMock();
        $mockCfg
            ->expects($this->any())
            ->method('collectionPeriod')
            ->will($this->returnValue($mockCollPeriod));
        $mockCfg
            ->expects($this->any())
            ->method('collectionLogCalc')
            ->will($this->returnValue($mockCollCalc));
        /* setup Config */
        Config::set($mockCfg);
        /**
         * Perform test.
         */
        /** @var  $call Praxigento_Bonus_Service_Period_Call */
        $call = Config::get()->servicePeriod();
        $req  = $call->requestRegisterPeriodCalculation();
        $req->setPeriodValue($PERIOD_VAL);
        $req->setTypeCalcId($CALC_TYPE_ID);
        $req->setTypePeriodId($PERIOD_TYPE_ID);
        $resp = $call->registerPeriodCalculation($req);
        $this->assertTrue($resp->isSucceed());
        $this->assertEquals($FOUND_PERIOD_ID, $resp->getPeriod()->getId());
        $this->assertEquals($FOUND_CALC_ID, $resp->getLogCalc()->getId());
    }

    public function test_registerPeriodCalculation_foundPeriod_noLog() {
        $PERIOD_VAL      = '20150601';
        $CALC_TYPE_ID    = 21;
        $PERIOD_TYPE_ID  = 54;
        $FOUND_PERIOD_ID = 999;
        $NEW_CALC_ID     = 888;
        /**
         * Create mocks.
         */
        /* found period */
        $mockPeriod = $this
            ->getMockBuilder('Praxigento_Bonus_Model_Own_Period')
            ->setMethods(array( 'getId' ))
            ->getMock();
        $mockPeriod
            ->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($FOUND_PERIOD_ID));
        /* periods collection */
        $mockCollPeriod = $this
            ->getMockBuilder('Praxigento_Bonus_Resource_Own_Period_Collection')
            ->setMethods(array( 'getSize', 'getFirstItem' ))
            ->getMock();
        $mockCollPeriod
            ->expects($this->once())
            ->method('getSize')
            ->will($this->returnValue(1));
        $mockCollPeriod
            ->expects($this->once())
            ->method('getFirstItem')
            ->will($this->returnValue($mockPeriod));
        /* calculations collection */
        $mockCollCalc = $this
            ->getMockBuilder('Praxigento_Bonus_Resource_Own_Log_Calc_Collection')
            ->setMethods(array( 'getSize' ))
            ->getMock();
        $mockCollCalc
            ->expects($this->once())
            ->method('getSize')
            ->will($this->returnValue(0));
        /* calculation model to be saved */
        $mockCalc = $this
            ->getMockBuilder('Praxigento_Bonus_Model_Own_Log_Calc')
            ->setMethods(array( 'getId', 'save' ))
            ->getMock();
        $mockCalc
            ->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($NEW_CALC_ID));
        $mockCalc
            ->expects($this->once())
            ->method('save');
        /* config */
        $mockCfg = $this
            ->getMockBuilder('Praxigento_Bonus_Config')
            ->setMethods(array( 'collectionPeriod', 'collectionLogCalc', 'modelLogCalc' ))
            ->getMock();
        $mockCfg
            ->expects($this->any())
            ->method('collectionPeriod')
            ->will($this->returnValue($mockCollPeriod));
        $mockCfg
            ->expects($this->any())
            ->method('collectionLogCalc')
            ->will($this->returnValue($mockCollCalc));
        $mockCfg
            ->expects($this->any())
            ->method('modelLogCalc')
            ->will($this->returnValue($mockCalc));
        /* setup Config */
        Config::set($mockCfg);
        /**
         * Perform test.
         */
        /** @var  $call Praxigento_Bonus_Service_Period_Call */
        $call = Config::get()->servicePeriod();
        $req  = $call->requestRegisterPeriodCalculation();
        $req->setPeriodValue($PERIOD_VAL);
        $req->setTypeCalcId($CALC_TYPE_ID);
        $req->setTypePeriodId($PERIOD_TYPE_ID);
        $resp = $call->registerPeriodCalculation($req);
        $this->assertEquals($FOUND_PERIOD_ID, $resp->getPeriod()->getId());
        $this->assertEquals($NEW_CALC_ID, $resp->getLogCalc()->getId());
    }

    public function test_registerPeriodCalculation_isPeriod_noLog() {
        $PERIOD_ID = 21;
        $LOG_ID    = 34;
        /**
         * Create mocks.
         */
        /* Period Model */
        $mockPeriod = $this
            ->getMockBuilder('Praxigento_Bonus_Model_Own_Period')
            ->setMethods(array( 'load' ))
            ->getMock();
        $mockPeriod
            ->expects($this->once())
            ->method('load')
            ->with($this->equalTo($PERIOD_ID));
        $mockPeriod->setId($PERIOD_ID);
        /* Log Calc Model */
        $mockLogCalc = $this
            ->getMockBuilder('Praxigento_Bonus_Model_Own_Log_Calc')
            ->setMethods(array( 'setData', 'getId', 'save' ))
            ->getMock();
        $mockLogCalc
            ->expects($this->at(0))
            ->method('setData')
            ->with($this->equalTo(LogCalc::ATTR_PERIOD_ID), $this->equalTo($PERIOD_ID));
        $mockLogCalc
            ->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($LOG_ID));
        $mockLogCalc
            ->expects($this->once())
            ->method('save');
        /* config */
        $mockCfg = $this
            ->getMockBuilder('Praxigento_Bonus_Config')
            ->setMethods(array( 'modelPeriod', 'modelLogCalc' ))
            ->getMock();
        $mockCfg
            ->expects($this->any())
            ->method('modelPeriod')
            ->will($this->returnValue($mockPeriod));
        $mockCfg
            ->expects($this->any())
            ->method('modelLogCalc')
            ->will($this->returnValue($mockLogCalc));
        /* setup Config */
        Config::set($mockCfg);
        /** @var  $call Praxigento_Bonus_Service_Period_Call */
        $call = Config::get()->servicePeriod();
        $req  = $call->requestRegisterPeriodCalculation();
        $req->setPeriodId($PERIOD_ID);
        $req->setLogCalcId(null);
        $resp = $call->registerPeriodCalculation($req);
        $this->assertEquals($PERIOD_ID, $resp->getPeriod()->getId());
        $this->assertEquals($LOG_ID, $resp->getLogCalc()->getId());
    }

    public function test_registerPeriodCalculation_noPeriod_noLog_exception() {
        $PERIOD_VAL      = '20150601';
        $CALC_TYPE_ID    = 21;
        $PERIOD_TYPE_ID  = 54;
        $FOUND_PERIOD_ID = 999;
        $NEW_CALC_ID     = 888;
        /**
         * Create mocks.
         */
        /* Mock constructor related environment */
        /* Config:: */
        $mockCfg = $this
            ->getMockBuilder('Praxigento_Bonus_Config')
            ->setMethods(array( 'modelPeriod', 'modelLogCalc', 'collectionPeriod', 'connectionWrite' ))
            ->getMock();
        /* Mock runtime environment */
        // $period  = Config::get()->modelPeriod();
        $mockPeriod = $this
            ->getMockBuilder('Praxigento_Bonus_Model_Own_Period')
            ->setMethods(array( 'getResource' ))
            ->getMock();
        $mockCfg
            ->expects($this->any())
            ->method('modelPeriod')
            ->will($this->returnValue($mockPeriod));
        // $logCalc = Config::get()->modelLogCalc();
        $mockLogCalc = $this
            ->getMockBuilder('Praxigento_Bonus_Model_Own_Log_Calc')
            ->setMethods(array( 'getResource' ))
            ->getMock();
        $mockCfg
            ->expects($this->any())
            ->method('modelLogCalc')
            ->will($this->returnValue($mockLogCalc));
        // $periods = Config::get()->collectionPeriod();
        $mockPeriods = $this
            ->getMockBuilder('Praxigento_Bonus_Resource_Own_Period_Collection')
            ->setMethods(array( 'getSize' ))
            ->getMock();
        $mockCfg
            ->expects($this->any())
            ->method('collectionPeriod')
            ->will($this->returnValue($mockPeriods));
        // if($periods->getSize() == 0)
        $mockPeriods
            ->expects($this->once())
            ->method('getSize')
            ->will($this->returnValue(0));
        // $connection = Config::get()->connectionWrite();
        $mockConnection = $this
            ->getMockBuilder('Varien_Db_Adapter_Pdo_Mysql')
            ->disableOriginalConstructor()
            ->setMethods(array( 'beginTransaction', 'rollBack' ))
            ->getMock();
        $mockCfg
            ->expects($this->any())
            ->method('connectionWrite')
            ->will($this->returnValue($mockConnection));
        // $connection->beginTransaction();
        $mockConnection
            ->expects($this->once())
            ->method('beginTransaction');
        // $period->getResource()->save();
        $mockPeriodResource = $this
            ->getMockBuilder('Praxigento_Bonus_Resource_Own_Period')
            ->setMethods(array( 'save' ))
            ->getMock();
        $mockPeriod
            ->expects($this->any())
            ->method('getResource')
            ->will($this->returnValue($mockPeriodResource));
        $mockPeriodResource
            ->expects($this->once())
            ->method('save')
            ->will($this->throwException(new Exception));
        //  $connection->rollBack();
        $mockConnection
            ->expects($this->once())
            ->method('rollBack');
        /* resource as singleton to
        /**
         * Setup config and perform call.
         */
        Config::set($mockCfg);
        /** @var  $call Praxigento_Bonus_Service_Period_Call */
        $call = Config::get()->servicePeriod();
        $req  = $call->requestRegisterPeriodCalculation();
        $req->setPeriodValue($PERIOD_VAL);
        $req->setTypeCalcId($CALC_TYPE_ID);
        $req->setTypePeriodId($PERIOD_TYPE_ID);
        $call->registerPeriodCalculation($req);
    }

    public function test_registerPeriodCalculation_noPeriod_noLog() {
        $PERIOD_VAL     = '20150601';
        $CALC_TYPE_ID   = 21;
        $PERIOD_TYPE_ID = 54;
        /**
         * Create mocks (direct order).
         */
        /* Mock constructor related environment */
        /* Config:: */
        $mockCfg = $this
            ->getMockBuilder('Praxigento_Bonus_Config')
            ->setMethods(array( 'modelPeriod', 'modelLogCalc', 'collectionPeriod', 'connectionWrite' ))
            ->getMock();
        /* Mock runtime environment */
        // $period  = Config::get()->modelPeriod();
        $mockPeriod = $this
            ->getMockBuilder('Praxigento_Bonus_Model_Own_Period')
            ->setMethods(array( 'getResource' ))
            ->getMock();
        $mockCfg
            ->expects($this->any())
            ->method('modelPeriod')
            ->will($this->returnValue($mockPeriod));
        // $logCalc = Config::get()->modelLogCalc();
        $mockLogCalc = $this
            ->getMockBuilder('Praxigento_Bonus_Model_Own_Log_Calc')
            ->setMethods(array( 'getResource' ))
            ->getMock();
        $mockCfg
            ->expects($this->any())
            ->method('modelLogCalc')
            ->will($this->returnValue($mockLogCalc));
        // $periods = Config::get()->collectionPeriod();
        $mockPeriods = $this
            ->getMockBuilder('Praxigento_Bonus_Resource_Own_Period_Collection')
            ->setMethods(array( 'getSize' ))
            ->getMock();
        $mockCfg
            ->expects($this->any())
            ->method('collectionPeriod')
            ->will($this->returnValue($mockPeriods));
        // if($periods->getSize() == 0)
        $mockPeriods
            ->expects($this->once())
            ->method('getSize')
            ->will($this->returnValue(0));
        // $connection = Config::get()->connectionWrite();
        $mockConnection = $this
            ->getMockBuilder('Varien_Db_Adapter_Pdo_Mysql')
            ->disableOriginalConstructor()
            ->setMethods(array( 'beginTransaction', 'commit' ))
            ->getMock();
        $mockCfg
            ->expects($this->any())
            ->method('connectionWrite')
            ->will($this->returnValue($mockConnection));
        // $connection->beginTransaction();
        $mockConnection
            ->expects($this->once())
            ->method('beginTransaction');
        // $period->getResource()->save();
        $mockPeriodResource = $this
            ->getMockBuilder('Praxigento_Bonus_Resource_Own_Period')
            ->setMethods(array( 'save' ))
            ->getMock();
        $mockPeriod
            ->expects($this->any())
            ->method('getResource')
            ->will($this->returnValue($mockPeriodResource));
        $mockPeriodResource
            ->expects($this->once())
            ->method('save');
        // $logCalc->getResource()->save();
        $mockLogCalcResource = $this
            ->getMockBuilder('Praxigento_Bonus_Resource_Own_Log_Calc')
            ->setMethods(array( 'save' ))
            ->getMock();
        $mockLogCalc
            ->expects($this->any())
            ->method('getResource')
            ->will($this->returnValue($mockLogCalcResource));
        $mockLogCalcResource
            ->expects($this->once())
            ->method('save');
        // $connection->commit();
        $mockConnection
            ->expects($this->once())
            ->method('commit');
        /**
         * Setup config and perform call.
         */
        Config::set($mockCfg);
        /** @var  $call Praxigento_Bonus_Service_Period_Call */
        $call = Config::get()->servicePeriod();
        $req  = $call->requestRegisterPeriodCalculation();
        $req->setPeriodValue($PERIOD_VAL);
        $req->setTypeCalcId($CALC_TYPE_ID);
        $req->setTypePeriodId($PERIOD_TYPE_ID);
        $call->registerPeriodCalculation($req);
    }

    /**
     * We should return first period in 'complete' status.
     */
    public function test_getPeriodForPvWriteOff_isPeriod_Complete() {
        $VAL      = '20150601';
        $VAL_NEXT = '20150602';
        /**
         * Create mocks.
         */
        /* 'processing' period collection should return no data */
        $mockPeriodCollP = $this
            ->getMockBuilder('Praxigento_Bonus_Resource_Own_Period_Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $mockPeriodCollP
            ->expects($this->once())
            ->method('getSize')
            ->will($this->returnValue(0));
        $mockPeriodCollC = $this
            ->getMockBuilder('Praxigento_Bonus_Resource_Own_Period_Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $mockPeriodCollC
            ->expects($this->once())
            ->method('getSize')
            ->will($this->returnValue(4));
        /* add period item to period collection */
        $mockPeriod = $this
            ->getMockBuilder('Praxigento_Bonus_Model_Own_Period')
            ->disableOriginalConstructor()
            ->getMock();
        $mockPeriod
            ->expects($this->once())->method('getData')
            ->with($this->equalTo(Period::ATTR_VALUE))
            ->will($this->returnValue($VAL));
        $mockPeriodCollC
            ->expects($this->once())
            ->method('getFirstItem')
            ->will($this->returnValue($mockPeriod));
        /* config */
        $mockCfg = $this
            ->getMockBuilder('Praxigento_Bonus_Config')
            ->setMethods(array( 'collectionPeriod' ))
            ->getMock();
        $mockCfg
            ->expects($this->at(0))
            ->method('collectionPeriod')
            ->will($this->returnValue($mockPeriodCollP));
        $mockCfg
            ->expects($this->at(1))
            ->method('collectionPeriod')
            ->will($this->returnValue($mockPeriodCollC));
        /* setup Config */
        Config::set($mockCfg);
        /**
         * Compose service, prepare request and perform call.
         */
        /** @var  $req  Praxigento_Bonus_Service_Period_Request_GetPeriodForPvWriteOff */
        $req  = Mage::getModel('prxgt_bonus_service/period_request_getPeriodForPvWriteOff');
        $call = Config::get()->servicePeriod();
        /** @var  $resp Praxigento_Bonus_Service_Period_Response_GetPeriodForPvWriteOff */
        $resp = $call->getPeriodForPvWriteOff($req);
        $this->assertTrue($resp->isSucceed());
        $this->assertEquals($VAL_NEXT, $resp->getPeriodValue());
        $this->assertNull($resp->getExistingPeriodId());
    }

    /**
     * We should return first period in 'processing' status.
     */
    public function test_getPeriodForPvWriteOff_isPeriod_Processing() {
        $VAL = '20150601';
        $ID  = 256;
        /**
         * Create mocks.
         */
        /* period collection should return data */
        $mockPeriodColl = $this
            ->getMockBuilder('Praxigento_Bonus_Resource_Own_Period_Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $mockPeriodColl
            ->expects($this->once())
            ->method('getSize')
            ->will($this->returnValue(500));
        /* add period item to period collection */
        $mockPeriod = $this
            ->getMockBuilder('Praxigento_Bonus_Model_Own_Period')
            ->disableOriginalConstructor()
            ->getMock();
        $mockPeriod
            ->expects($this->at(0))->method('getData')
            ->with($this->equalTo(Period::ATTR_ID))
            ->will($this->returnValue($ID));
        $mockPeriod
            ->expects($this->at(1))->method('getData')
            ->with($this->equalTo(Period::ATTR_VALUE))
            ->will($this->returnValue($VAL));
        $mockPeriodColl
            ->expects($this->once())
            ->method('getFirstItem')
            ->will($this->returnValue($mockPeriod));
        /* config */
        $mockCfg = $this
            ->getMockBuilder('Praxigento_Bonus_Config')
            ->setMethods(array( 'collectionPeriod' ))
            ->getMock();
        $mockCfg
            ->expects($this->at(0))
            ->method('collectionPeriod')
            ->will($this->returnValue($mockPeriodColl));
        /* setup Config */
        Config::set($mockCfg);
        /**
         * Compose service, prepare request and perform call.
         */
        /** @var  $req  Praxigento_Bonus_Service_Period_Request_GetPeriodForPvWriteOff */
        $req  = Mage::getModel('prxgt_bonus_service/period_request_getPeriodForPvWriteOff');
        $call = Config::get()->servicePeriod();
        /** @var  $resp Praxigento_Bonus_Service_Period_Response_GetPeriodForPvWriteOff */
        $resp = $call->getPeriodForPvWriteOff($req);
        $this->assertTrue($resp->isSucceed());
        $this->assertEquals($VAL, $resp->getPeriodValue());
        $this->assertEquals($ID, $resp->getExistingPeriodId());
    }


    /**
     * We should calculate period value based on transaction date when there are no periods in db but there are
     * transactions.
     */
    public function test_getPeriodForPvWriteOff_noPeriods_areTransactions() {
        /**
         * Create mocks.
         */
        /* period collection should return no data 2 times */
        $mockPeriodColl = $this
            ->getMockBuilder('Praxigento_Bonus_Resource_Own_Period_Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $mockPeriodColl
            ->expects($this->exactly(2))
            ->method('getSize')
            ->will($this->returnValue(0));
        /* transaction collection should return data */
        $mockTransactionColl = $this
            ->getMockBuilder('Praxigento_Bonus_Resource_Own_Transaction_Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $mockTransactionColl
            ->expects($this->once())
            ->method('getSize')
            ->will($this->returnValue(1));
        /* add transaction item to collection */
        $mockTransaction = $this
            ->getMockBuilder('Praxigento_Bonus_Model_Own_Transaction')
            ->disableOriginalConstructor()
            ->getMock();
        $mockTransaction
            ->expects($this->once())->method('getData')
            ->with($this->equalTo(Transaction::ATTR_DATE_APPLIED))
            ->will($this->returnValue('2015-06-01 07:00:00'));
        $mockTransactionColl
            ->expects($this->once())
            ->method('getFirstItem')
            ->will($this->returnValue($mockTransaction));
        /* config */
        $mockCfg = $this
            ->getMockBuilder('Praxigento_Bonus_Config')
            ->setMethods(array( 'collectionPeriod', 'collectionTransaction' ))
            ->getMock();
        $mockCfg
            ->expects($this->any())
            ->method('collectionPeriod')
            ->will($this->returnValue($mockPeriodColl));
        $mockCfg
            ->expects($this->any())
            ->method('collectionTransaction')
            ->will($this->returnValue($mockTransactionColl));
        /* setup Config */
        Config::set($mockCfg);
        /**
         * Compose service, prepare request and perform call.
         */
        /** @var  $req  Praxigento_Bonus_Service_Period_Request_GetPeriodForPvWriteOff */
        $req  = Mage::getModel('prxgt_bonus_service/period_request_getPeriodForPvWriteOff');
        $call = Config::get()->servicePeriod();
        /** @var  $resp Praxigento_Bonus_Service_Period_Response_GetPeriodForPvWriteOff */
        $resp = $call->getPeriodForPvWriteOff($req);
        $this->assertTrue($resp->isSucceed());
        $this->assertEquals('20150601', $resp->getPeriodValue());
        $this->assertNull($resp->getExistingPeriodId());
    }

    /**
     * NOTHING_TO_DO error code is expected when there are no period or transactions in DB.
     */
    public function test_getPeriodForPvWriteOff_noPeriods_noTransactions() {
        /**
         * Create mocks.
         */
        /* period collection should return no data 2 times */
        $mockPeriodColl = $this
            ->getMockBuilder('Praxigento_Bonus_Resource_Own_Period_Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $mockPeriodColl
            ->expects($this->exactly(2))
            ->method('getSize')
            ->will($this->returnValue(0));
        /* transaction collection should return no data */
        $mockTransactionColl = $this
            ->getMockBuilder('Praxigento_Bonus_Resource_Own_Transaction_Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $mockTransactionColl
            ->expects($this->once())
            ->method('getSize')
            ->will($this->returnValue(0));
        /* config */
        $mockCfg = $this
            ->getMockBuilder('Praxigento_Bonus_Config')
            ->setMethods(array( 'collectionPeriod', 'collectionTransaction' ))
            ->getMock();
        $mockCfg
            ->expects($this->any())
            ->method('collectionPeriod')
            ->will($this->returnValue($mockPeriodColl));
        $mockCfg
            ->expects($this->any())
            ->method('collectionTransaction')
            ->will($this->returnValue($mockTransactionColl));
        /* setup Config */
        Config::set($mockCfg);
        /**
         * Compose service, prepare request and perform call.
         */
        $call = Config::get()->servicePeriod();
        /** @var  $req  Praxigento_Bonus_Service_Period_Request_GetPeriodForPvWriteOff */
        $req = Mage::getModel('prxgt_bonus_service/period_request_getPeriodForPvWriteOff');
        /** @var  $resp Praxigento_Bonus_Service_Period_Response_GetPeriodForPvWriteOff */
        $resp = $call->getPeriodForPvWriteOff($req);
        $this->assertFalse($resp->isSucceed());
        $this->assertEquals(GetPeriodForPvWriteOff::ERR_NOTHING_TO_DO, $resp->getErrorCode());
    }

    /**
     * NOTHING_TO_DO error code is expected when there are no period or transactions in DB.
     */
    public function test_getPeriodForPersonalBonus_noPeriods_noTransactions() {
        /**
         * Create mocks.
         */
        /* period collection should return no data 2 times */
        $mockPeriodColl = $this
            ->getMockBuilder('Praxigento_Bonus_Resource_Own_Period_Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $mockPeriodColl
            ->expects($this->exactly(2))
            ->method('getSize')
            ->will($this->returnValue(0));
        /* transaction collection should return no data */
        $mockTransactionColl = $this
            ->getMockBuilder('Praxigento_Bonus_Resource_Own_Transaction_Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $mockTransactionColl
            ->expects($this->once())
            ->method('getSize')
            ->will($this->returnValue(0));
        /* config */
        $mockCfg = $this
            ->getMockBuilder('Praxigento_Bonus_Config')
            ->setMethods(array( 'collectionPeriod', 'collectionTransaction' ))
            ->getMock();
        $mockCfg
            ->expects($this->any())
            ->method('collectionPeriod')
            ->will($this->returnValue($mockPeriodColl));
        $mockCfg
            ->expects($this->any())
            ->method('collectionTransaction')
            ->will($this->returnValue($mockTransactionColl));
        /* setup Config */
        Config::set($mockCfg);
        /**
         * Compose service, prepare request and perform call.
         */
        $call = Config::get()->servicePeriod();
        /** @var  $req  Praxigento_Bonus_Service_Period_Request_GetPeriodForPersonalBonus */
        $req = Mage::getModel('prxgt_bonus_service/period_request_getPeriodForPersonalBonus');
        /** @var  $resp Praxigento_Bonus_Service_Period_Response_GetPeriodForPersonalBonus */
        $resp = $call->getPeriodForPersonalBonus($req);
        $this->assertFalse($resp->isSucceed());
        $this->assertEquals(GetPeriodForPersonalBonus::ERR_NOTHING_TO_DO, $resp->getErrorCode());
    }

    /**
     * We should calculate period value based on transaction date when there are no periods in db but there are
     * transactions.
     */
    public function test_getPeriodForPersonalBonus_noPeriods_areTransactions() {
        /**
         * Create mocks.
         */
        /* period collection should return no data 2 times */
        $mockPeriodColl = $this
            ->getMockBuilder('Praxigento_Bonus_Resource_Own_Period_Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $mockPeriodColl
            ->expects($this->exactly(2))
            ->method('getSize')
            ->will($this->returnValue(0));
        /* transaction collection should return data */
        $mockTransactionColl = $this
            ->getMockBuilder('Praxigento_Bonus_Resource_Own_Transaction_Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $mockTransactionColl
            ->expects($this->once())
            ->method('getSize')
            ->will($this->returnValue(1));
        /* add transaction item to collection */
        $mockTransaction = $this
            ->getMockBuilder('Praxigento_Bonus_Model_Own_Transaction')
            ->disableOriginalConstructor()
            ->getMock();
        $mockTransaction
            ->expects($this->once())->method('getData')
            ->with($this->equalTo(Transaction::ATTR_DATE_APPLIED))
            ->will($this->returnValue('2015-06-01 07:00:00'));
        $mockTransactionColl
            ->expects($this->once())
            ->method('getFirstItem')
            ->will($this->returnValue($mockTransaction));
        /* config */
        $mockCfg = $this
            ->getMockBuilder('Praxigento_Bonus_Config')
            ->setMethods(array( 'collectionPeriod', 'collectionTransaction' ))
            ->getMock();
        $mockCfg
            ->expects($this->any())
            ->method('collectionPeriod')
            ->will($this->returnValue($mockPeriodColl));
        $mockCfg
            ->expects($this->any())
            ->method('collectionTransaction')
            ->will($this->returnValue($mockTransactionColl));
        /* setup Config */
        Config::set($mockCfg);
        /**
         * Compose service, prepare request and perform call.
         */
        $call = Config::get()->servicePeriod();
        /** @var  $req  Praxigento_Bonus_Service_Period_Request_GetPeriodForPersonalBonus */
        $req = Mage::getModel('prxgt_bonus_service/period_request_getPeriodForPersonalBonus');
        /** @var  $resp Praxigento_Bonus_Service_Period_Response_GetPeriodForPersonalBonus */
        $resp = $call->getPeriodForPersonalBonus($req);
        $this->assertTrue($resp->isSucceed());
        $this->assertEquals('20150601', $resp->getPeriodValue());
        $this->assertNull($resp->getExistingPeriodId());
    }

    /**
     * We should return first period in 'processing' status.
     */
    public function test_getPeriodForPersonalBonus_isPeriod_Processing() {
        $VAL = '20150601';
        $ID  = 256;
        /**
         * Create mocks.
         */
        /* period collection should return data */
        $mockPeriodColl = $this
            ->getMockBuilder('Praxigento_Bonus_Resource_Own_Period_Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $mockPeriodColl
            ->expects($this->once())
            ->method('getSize')
            ->will($this->returnValue(500));
        /* add period item to period collection */
        $mockPeriod = $this
            ->getMockBuilder('Praxigento_Bonus_Model_Own_Period')
            ->disableOriginalConstructor()
            ->getMock();
        $mockPeriod
            ->expects($this->at(0))->method('getData')
            ->with($this->equalTo(Period::ATTR_ID))
            ->will($this->returnValue($ID));
        $mockPeriod
            ->expects($this->at(1))->method('getData')
            ->with($this->equalTo(Period::ATTR_VALUE))
            ->will($this->returnValue($VAL));
        $mockPeriodColl
            ->expects($this->once())
            ->method('getFirstItem')
            ->will($this->returnValue($mockPeriod));
        /* config */
        $mockCfg = $this
            ->getMockBuilder('Praxigento_Bonus_Config')
            ->setMethods(array( 'collectionPeriod' ))
            ->getMock();
        $mockCfg
            ->expects($this->at(0))
            ->method('collectionPeriod')
            ->will($this->returnValue($mockPeriodColl));
        /* setup Config */
        Config::set($mockCfg);
        /**
         * Compose service, prepare request and perform call.
         */
        $call = Config::get()->servicePeriod();
        /** @var  $req  Praxigento_Bonus_Service_Period_Request_GetPeriodForPersonalBonus */
        $req = Mage::getModel('prxgt_bonus_service/period_request_getPeriodForPersonalBonus');
        /** @var  $resp Praxigento_Bonus_Service_Period_Response_GetPeriodForPersonalBonus */
        $resp = $call->getPeriodForPersonalBonus($req);
        $this->assertTrue($resp->isSucceed());
        $this->assertEquals($VAL, $resp->getPeriodValue());
        $this->assertEquals(Config::PERIOD_DAY, $resp->getPeriodTypeCode());
        $this->assertEquals($ID, $resp->getExistingPeriodId());
    }

    /**
     * We should return first period in 'complete' status.
     */
    public function test_getPeriodForPersonalBonus_isPeriod_Complete() {
        $VAL      = '20150601';
        $VAL_NEXT = '20150602';
        /**
         * Create mocks.
         */
        /* 'processing' period collection should return no data */
        $mockPeriodCollP = $this
            ->getMockBuilder('Praxigento_Bonus_Resource_Own_Period_Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $mockPeriodCollP
            ->expects($this->once())
            ->method('getSize')
            ->will($this->returnValue(0));
        $mockPeriodCollC = $this
            ->getMockBuilder('Praxigento_Bonus_Resource_Own_Period_Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $mockPeriodCollC
            ->expects($this->once())
            ->method('getSize')
            ->will($this->returnValue(4));
        /* add period item to period collection */
        $mockPeriod = $this
            ->getMockBuilder('Praxigento_Bonus_Model_Own_Period')
            ->disableOriginalConstructor()
            ->getMock();
        $mockPeriod
            ->expects($this->once())->method('getData')
            ->with($this->equalTo(Period::ATTR_VALUE))
            ->will($this->returnValue($VAL));
        $mockPeriodCollC
            ->expects($this->once())
            ->method('getFirstItem')
            ->will($this->returnValue($mockPeriod));
        /* config */
        $mockCfg = $this
            ->getMockBuilder('Praxigento_Bonus_Config')
            ->setMethods(array( 'collectionPeriod' ))
            ->getMock();
        $mockCfg
            ->expects($this->at(0))
            ->method('collectionPeriod')
            ->will($this->returnValue($mockPeriodCollP));
        $mockCfg
            ->expects($this->at(1))
            ->method('collectionPeriod')
            ->will($this->returnValue($mockPeriodCollC));
        /* setup Config */
        Config::set($mockCfg);
        /**
         * Compose service, prepare request and perform call.
         */
        $call = Config::get()->servicePeriod();
        /** @var  $req  Praxigento_Bonus_Service_Period_Request_GetPeriodForPersonalBonus */
        $req = Mage::getModel('prxgt_bonus_service/period_request_getPeriodForPersonalBonus');
        /** @var  $resp Praxigento_Bonus_Service_Period_Response_GetPeriodForPersonalBonus */
        $resp = $call->getPeriodForPersonalBonus($req);
        $this->assertTrue($resp->isSucceed());
        $this->assertEquals($VAL_NEXT, $resp->getPeriodValue());
        $this->assertNull($resp->getExistingPeriodId());
    }

}