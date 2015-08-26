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
class Praxigento_Bonus_Test_Service_Period_Call_UnitTest extends PHPUnit_Framework_TestCase
{

    public function test_constructor()
    {
        /** @var  $call Praxigento_Bonus_Service_Period_Call */
        $call = Config::get()->servicePeriod();
        $this->assertNotNull($call);
        $this->assertTrue($call->initPeriodCollection() instanceof Praxigento_Bonus_Resource_Own_Period_Collection);
        $this->assertTrue($call->initTransactionCollection() instanceof Praxigento_Bonus_Resource_Own_Transaction_Collection);
    }

    public function test_requests()
    {
        /** @var  $call Praxigento_Bonus_Service_Period_Call */
        $call = Config::get()->servicePeriod();
        $req = $call->requestPeriodForPersonalBonus();
        $this->assertTrue($req instanceof Praxigento_Bonus_Service_Period_Request_GetPeriodForPersonalBonus);
        $req = $call->requestPeriodForPvWriteOff();
        $this->assertTrue($req instanceof Praxigento_Bonus_Service_Period_Request_GetPeriodForPvWriteOff);
        $req = $call->requestRegisterPeriodCalculation();
        $this->assertTrue($req instanceof Praxigento_Bonus_Service_Period_Request_RegisterPeriodCalculation);
    }


    public function test_registerPeriodCalculation_isPeriod_isLog()
    {
        $PERIOD_ID = 21;
        $LOG_ID = 34;
        /**
         * Create mocks.
         */
        /* Period Model */
        $mockPeriod = $this->getMockBuilder('Praxigento_Bonus_Model_Own_Period')
            ->setMethods(array('load'))
            ->getMock();
        $mockPeriod
            ->expects($this->once())
            ->method('load')
            ->with($this->equalTo($PERIOD_ID));
        $mockPeriod->setId($PERIOD_ID);
        /* Log Calc Model */
        $mockLogCalc = $this->getMockBuilder('Praxigento_Bonus_Model_Own_Log_Calc')
            ->setMethods(array('load'))
            ->getMock();
        $mockLogCalc
            ->expects($this->once())
            ->method('load')
            ->with($this->equalTo($LOG_ID));
        $mockLogCalc->setId($LOG_ID);
        /* config */
        $mockBuilder = $this->getMockBuilder('Praxigento_Bonus_Config');
        $mockBuilder->setMethods(array('modelPeriod', 'modelLogCalc'));
        $mockCfg = $mockBuilder->getMock();
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
        $req = $call->requestRegisterPeriodCalculation();
        $req->setPeriodId($PERIOD_ID);
        $req->setLogCalcId($LOG_ID);
        $resp = $call->registerPeriodCalculation($req);
        $this->assertEquals($PERIOD_ID, $resp->getPeriod()->getId());
        $this->assertEquals($LOG_ID, $resp->getLogCalc()->getId());
    }

    public function test_registerPeriodCalculation_isPeriod_noLog()
    {
        $PERIOD_ID = 21;
        $LOG_ID = 34;
        /**
         * Create mocks.
         */
        /* Period Model */
        $mockPeriod = $this->getMockBuilder('Praxigento_Bonus_Model_Own_Period')
            ->setMethods(array('load'))
            ->getMock();
        $mockPeriod
            ->expects($this->once())
            ->method('load')
            ->with($this->equalTo($PERIOD_ID));
        $mockPeriod->setId($PERIOD_ID);
        /* Log Calc Model */
        $mockLogCalc = $this->getMockBuilder('Praxigento_Bonus_Model_Own_Log_Calc')
            ->setMethods(array('setData', 'getId', 'save'))
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
        $mockBuilder = $this->getMockBuilder('Praxigento_Bonus_Config');
        $mockBuilder->setMethods(array('modelPeriod', 'modelLogCalc'));
        $mockCfg = $mockBuilder->getMock();
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
        $req = $call->requestRegisterPeriodCalculation();
        $req->setPeriodId($PERIOD_ID);
        $req->setLogCalcId(null);
        $resp = $call->registerPeriodCalculation($req);
        $this->assertEquals($PERIOD_ID, $resp->getPeriod()->getId());
        $this->assertEquals($LOG_ID, $resp->getLogCalc()->getId());
    }

    /**
     * We should return first period in 'complete' status.
     */
    public function test_getPeriodForPvWriteOff_isPeriod_Complete()
    {
        $VAL = '20150601';
        $VAL_NEXT = '20150602';
        /**
         * Create mocks.
         */
        /* 'processing' period collection should return no data */
        $mockPeriodCollP = $this->mockPeriodCollection();
        $mockPeriodCollP->expects($this->once())->method('getSize')->will($this->returnValue(0));
        $mockPeriodCollC = $this->mockPeriodCollection();
        $mockPeriodCollC->expects($this->once())->method('getSize')->will($this->returnValue(4));
        /* add period item to period collection */
        $mockPeriod = $this->mockPeriod();
        $mockPeriod->expects($this->once())->method('getData')
            ->with($this->equalTo(Period::ATTR_VALUE))
            ->will($this->returnValue($VAL));
        $mockPeriodCollC->expects($this->once())->method('getFirstItem')->will($this->returnValue($mockPeriod));
        /**
         * Compose service.
         */
        $mockCall = $this->mockCall(array('initPeriodCollection'));
        $mockCall->expects($this->at(0))->method('initPeriodCollection')->will($this->returnValue($mockPeriodCollP));
        $mockCall->expects($this->at(1))->method('initPeriodCollection')->will($this->returnValue($mockPeriodCollC));
        /**
         * Prepare request and perform call.
         */
        /** @var  $req  Praxigento_Bonus_Service_Period_Request_GetPeriodForPvWriteOff */
        $req = Mage::getModel('prxgt_bonus_service/period_request_getPeriodForPvWriteOff');
        /** @var  $resp Praxigento_Bonus_Service_Period_Response_GetPeriodForPvWriteOff */
        $resp = $mockCall->getPeriodForPvWriteOff($req);
        $this->assertTrue($resp->isSucceed());
        $this->assertEquals($VAL_NEXT, $resp->getPeriodValue());
        $this->assertNull($resp->getExistingPeriodId());
    }

    /**
     * Create empty mock with disabled constructor.
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function mockPeriodCollection()
    {
        $mockBuilder = $this->getMockBuilder('Praxigento_Bonus_Resource_Own_Period_Collection');
        $result = $mockBuilder
            ->disableOriginalConstructor()
            ->getMock();
        return $result;
    }

    /**
     * Create empty mock with disabled constructor.
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function mockPeriod($methods = null)
    {
        $mockBuilder = $this->getMockBuilder('Praxigento_Bonus_Model_Own_Period');
        if (is_array($methods)) {
            $mockBuilder->setMethods($methods);
        }
        $result = $mockBuilder
            ->disableOriginalConstructor()
            ->getMock();
        return $result;
    }

    /**
     * @param $methods array of methods to be mocked.
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function mockCall($methods)
    {
        $mockBuilder = $this->getMockBuilder('Praxigento_Bonus_Service_Period_Call');
        $result = $mockBuilder
            ->setMethods($methods)
            ->getMock();
        return $result;
    }

    /**
     * We should return first period in 'processing' status.
     */
    public function test_getPeriodForPvWriteOff_isPeriod_Processing()
    {
        $VAL = '20150601';
        $ID = 256;
        /**
         * Create mocks.
         */
        /* period collection should return data */
        $mockPeriodColl = $this->mockPeriodCollection();
        $mockPeriodColl->expects($this->once())->method('getSize')->will($this->returnValue(500));
        /* add period item to period collection */
        $mockPeriod = $this->mockPeriod();
        $mockPeriod->expects($this->at(0))->method('getData')
            ->with($this->equalTo(Period::ATTR_ID))
            ->will($this->returnValue($ID));
        $mockPeriod->expects($this->at(1))->method('getData')
            ->with($this->equalTo(Period::ATTR_VALUE))
            ->will($this->returnValue($VAL));
        $mockPeriodColl->expects($this->once())->method('getFirstItem')->will($this->returnValue($mockPeriod));
        /**
         * Compose service.
         */
        $mockCall = $this->mockCall(array('initPeriodCollection'));
        $mockCall->expects($this->any())->method('initPeriodCollection')->will($this->returnValue($mockPeriodColl));
        /**
         * Prepare request and perform call.
         */
        /** @var  $req  Praxigento_Bonus_Service_Period_Request_GetPeriodForPvWriteOff */
        $req = Mage::getModel('prxgt_bonus_service/period_request_getPeriodForPvWriteOff');
        /** @var  $resp Praxigento_Bonus_Service_Period_Response_GetPeriodForPvWriteOff */
        $resp = $mockCall->getPeriodForPvWriteOff($req);
        $this->assertTrue($resp->isSucceed());
        $this->assertEquals($VAL, $resp->getPeriodValue());
        $this->assertEquals($ID, $resp->getExistingPeriodId());
    }


    /**
     * We should calculate period value based on transaction date when there are no periods in db but there are
     * transactions.
     */
    public function test_getPeriodForPvWriteOff_noPeriods_areTransactions()
    {
        /**
         * Create mocks.
         */
        /* period collection should return no data 2 times */
        $mockPeriodColl = $this->mockPeriodCollection();
        $mockPeriodColl->expects($this->exactly(2))->method('getSize')->will($this->returnValue(0));
        /* transaction collection should return data */
        $mockTransactionColl = $this->mockTransactionCollection();
        $mockTransactionColl->expects($this->once())->method('getSize')->will($this->returnValue(1));
        /* add transaction item to collection */
        $mockTransaction = $this->mockTransaction();
        $mockTransaction->expects($this->once())->method('getData')
            ->with($this->equalTo(Transaction::ATTR_DATE_APPLIED))
            ->will($this->returnValue('2015-06-01 07:00:00'));
        $mockTransactionColl->expects($this->once())->method('getFirstItem')->will($this->returnValue($mockTransaction));
        /**
         * Compose service.
         */
        $mockCall = $this->mockCall(array('initPeriodCollection', 'initTransactionCollection'));
        $mockCall->expects($this->any())->method('initPeriodCollection')->will($this->returnValue($mockPeriodColl));
        $mockCall->expects($this->any())->method('initTransactionCollection')->will($this->returnValue($mockTransactionColl));
        /**
         * Prepare request and perform call.
         */
        /** @var  $req  Praxigento_Bonus_Service_Period_Request_GetPeriodForPvWriteOff */
        $req = Mage::getModel('prxgt_bonus_service/period_request_getPeriodForPvWriteOff');
        /** @var  $resp Praxigento_Bonus_Service_Period_Response_GetPeriodForPvWriteOff */
        $resp = $mockCall->getPeriodForPvWriteOff($req);
        $this->assertTrue($resp->isSucceed());
        $this->assertEquals('20150601', $resp->getPeriodValue());
        $this->assertNull($resp->getExistingPeriodId());
    }

    /**
     * NOTHING_TO_DO error code is expected when there are no period or transactions in DB.
     */
    public function test_getPeriodForPvWriteOff_noPeriods_noTransactions()
    {
        /**
         * Create mocks.
         */
        /* period collection should return no data 2 times */
        $mockPeriodColl = $this->mockPeriodCollection();
        $mockPeriodColl->expects($this->exactly(2))->method('getSize')->will($this->returnValue(0));
        /* transaction collection should return no data */
        $mockTransactionColl = $this->mockTransactionCollection();
        $mockTransactionColl->expects($this->once())->method('getSize')->will($this->returnValue(0));
        /**
         * Compose service.
         */
        $mockCall = $this->mockCall(array('initPeriodCollection', 'initTransactionCollection'));
        $mockCall->expects($this->any())->method('initPeriodCollection')->will($this->returnValue($mockPeriodColl));
        $mockCall->expects($this->any())->method('initTransactionCollection')->will($this->returnValue($mockTransactionColl));
        /**
         * Prepare request and perform call.
         */
        /** @var  $req  Praxigento_Bonus_Service_Period_Request_GetPeriodForPvWriteOff */
        $req = Mage::getModel('prxgt_bonus_service/period_request_getPeriodForPvWriteOff');
        /** @var  $resp Praxigento_Bonus_Service_Period_Response_GetPeriodForPvWriteOff */
        $resp = $mockCall->getPeriodForPvWriteOff($req);
        $this->assertFalse($resp->isSucceed());
        $this->assertEquals(GetPeriodForPvWriteOff::ERR_NOTHING_TO_DO, $resp->getErrorCode());
    }

    /**
     * Create empty mock with disabled constructor.
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function mockTransactionCollection()
    {
        $mockBuilder = $this->getMockBuilder('Praxigento_Bonus_Resource_Own_Transaction_Collection');
        $result = $mockBuilder
            ->disableOriginalConstructor()
            ->getMock();
        return $result;
    }

    /**
     * NOTHING_TO_DO error code is expected when there are no period or transactions in DB.
     */
    public function test_getPeriodForPersonalBonus_noPeriods_noTransactions()
    {
        /**
         * Create mocks.
         */
        /* period collection should return no data 2 times */
        $mockPeriodColl = $this->mockPeriodCollection();
        $mockPeriodColl->expects($this->exactly(2))->method('getSize')->will($this->returnValue(0));
        /* transaction collection should return no data */
        $mockTransactionColl = $this->mockTransactionCollection();
        $mockTransactionColl->expects($this->once())->method('getSize')->will($this->returnValue(0));
        /**
         * Compose service.
         */
        $mockCall = $this->mockCall(array('initPeriodCollection', 'initTransactionCollection'));
        $mockCall->expects($this->any())->method('initPeriodCollection')->will($this->returnValue($mockPeriodColl));
        $mockCall->expects($this->any())->method('initTransactionCollection')->will($this->returnValue($mockTransactionColl));
        /**
         * Prepare request and perform call.
         */
        /** @var  $req  Praxigento_Bonus_Service_Period_Request_GetPeriodForPersonalBonus */
        $req = Mage::getModel('prxgt_bonus_service/period_request_getPeriodForPersonalBonus');
        /** @var  $resp Praxigento_Bonus_Service_Period_Response_GetPeriodForPersonalBonus */
        $resp = $mockCall->getPeriodForPersonalBonus($req);
        $this->assertFalse($resp->isSucceed());
        $this->assertEquals(GetPeriodForPersonalBonus::ERR_NOTHING_TO_DO, $resp->getErrorCode());
    }

    /**
     * We should calculate period value based on transaction date when there are no periods in db but there are
     * transactions.
     */
    public function test_getPeriodForPersonalBonus_noPeriods_areTransactions()
    {
        /**
         * Create mocks.
         */
        /* period collection should return no data 2 times */
        $mockPeriodColl = $this->mockPeriodCollection();
        $mockPeriodColl->expects($this->exactly(2))->method('getSize')->will($this->returnValue(0));
        /* transaction collection should return data */
        $mockTransactionColl = $this->mockTransactionCollection();
        $mockTransactionColl->expects($this->once())->method('getSize')->will($this->returnValue(1));
        /* add transaction item to collection */
        $mockTransaction = $this->mockTransaction();
        $mockTransaction->expects($this->once())->method('getData')
            ->with($this->equalTo(Transaction::ATTR_DATE_APPLIED))
            ->will($this->returnValue('2015-06-01 07:00:00'));
        $mockTransactionColl->expects($this->once())->method('getFirstItem')->will($this->returnValue($mockTransaction));
        /**
         * Compose service.
         */
        $mockCall = $this->mockCall(array('initPeriodCollection', 'initTransactionCollection'));
        $mockCall->expects($this->any())->method('initPeriodCollection')->will($this->returnValue($mockPeriodColl));
        $mockCall->expects($this->any())->method('initTransactionCollection')->will($this->returnValue($mockTransactionColl));
        /**
         * Prepare request and perform call.
         */
        /** @var  $req  Praxigento_Bonus_Service_Period_Request_GetPeriodForPersonalBonus */
        $req = Mage::getModel('prxgt_bonus_service/period_request_getPeriodForPersonalBonus');
        /** @var  $resp Praxigento_Bonus_Service_Period_Response_GetPeriodForPersonalBonus */
        $resp = $mockCall->getPeriodForPersonalBonus($req);
        $this->assertTrue($resp->isSucceed());
        $this->assertEquals('20150601', $resp->getPeriodValue());
        $this->assertNull($resp->getExistingPeriodId());
    }

    /**
     * Create empty mock with disabled constructor.
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function mockTransaction()
    {
        $mockBuilder = $this->getMockBuilder('Praxigento_Bonus_Model_Own_Transaction');
        $result = $mockBuilder
            ->disableOriginalConstructor()
            ->getMock();
        return $result;
    }

    /**
     * We should return first period in 'processing' status.
     */
    public function test_getPeriodForPersonalBonus_isPeriod_Processing()
    {
        $VAL = '20150601';
        $ID = 256;
        /**
         * Create mocks.
         */
        /* period collection should return data */
        $mockPeriodColl = $this->mockPeriodCollection();
        $mockPeriodColl->expects($this->once())->method('getSize')->will($this->returnValue(500));
        /* add period item to period collection */
        $mockPeriod = $this->mockPeriod();
        $mockPeriod->expects($this->at(0))->method('getData')
            ->with($this->equalTo(Period::ATTR_ID))
            ->will($this->returnValue($ID));
        $mockPeriod->expects($this->at(1))->method('getData')
            ->with($this->equalTo(Period::ATTR_VALUE))
            ->will($this->returnValue($VAL));
        $mockPeriodColl->expects($this->once())->method('getFirstItem')->will($this->returnValue($mockPeriod));
        /**
         * Compose service.
         */
        $mockCall = $this->mockCall(array('initPeriodCollection'));
        $mockCall->expects($this->any())->method('initPeriodCollection')->will($this->returnValue($mockPeriodColl));
        /**
         * Prepare request and perform call.
         */
        /** @var  $req  Praxigento_Bonus_Service_Period_Request_GetPeriodForPersonalBonus */
        $req = Mage::getModel('prxgt_bonus_service/period_request_getPeriodForPersonalBonus');
        /** @var  $resp Praxigento_Bonus_Service_Period_Response_GetPeriodForPersonalBonus */
        $resp = $mockCall->getPeriodForPersonalBonus($req);
        $this->assertTrue($resp->isSucceed());
        $this->assertEquals($VAL, $resp->getPeriodValue());
        $this->assertEquals($ID, $resp->getExistingPeriodId());
    }

    /**
     * We should return first period in 'complete' status.
     */
    public function test_getPeriodForPersonalBonus_isPeriod_Complete()
    {
        $VAL = '20150601';
        $VAL_NEXT = '20150602';
        /**
         * Create mocks.
         */
        /* 'processing' period collection should return no data */
        $mockPeriodCollP = $this->mockPeriodCollection();
        $mockPeriodCollP->expects($this->once())->method('getSize')->will($this->returnValue(0));
        $mockPeriodCollC = $this->mockPeriodCollection();
        $mockPeriodCollC->expects($this->once())->method('getSize')->will($this->returnValue(4));
        /* add period item to period collection */
        $mockPeriod = $this->mockPeriod();
        $mockPeriod->expects($this->once())->method('getData')
            ->with($this->equalTo(Period::ATTR_VALUE))
            ->will($this->returnValue($VAL));
        $mockPeriodCollC->expects($this->once())->method('getFirstItem')->will($this->returnValue($mockPeriod));
        /**
         * Compose service.
         */
        $mockCall = $this->mockCall(array('initPeriodCollection'));
        $mockCall->expects($this->at(0))->method('initPeriodCollection')->will($this->returnValue($mockPeriodCollP));
        $mockCall->expects($this->at(1))->method('initPeriodCollection')->will($this->returnValue($mockPeriodCollC));
        /**
         * Prepare request and perform call.
         */
        /** @var  $req  Praxigento_Bonus_Service_Period_Request_GetPeriodForPersonalBonus */
        $req = Mage::getModel('prxgt_bonus_service/period_request_getPeriodForPersonalBonus');
        /** @var  $resp Praxigento_Bonus_Service_Period_Response_GetPeriodForPersonalBonus */
        $resp = $mockCall->getPeriodForPersonalBonus($req);
        $this->assertTrue($resp->isSucceed());
        $this->assertEquals($VAL_NEXT, $resp->getPeriodValue());
        $this->assertNull($resp->getExistingPeriodId());
    }

}