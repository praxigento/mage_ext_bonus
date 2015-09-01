<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;
use Praxigento_Bonus_Model_Own_Balance as Balance;

include_once('../../phpunit_bootstrap.php');

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Test_Service_Operations_Call_UnitTest extends PHPUnit_Framework_TestCase {

    public function test_constructor() {
        /** @var  $call Praxigento_Bonus_Service_Operations_Call */
        $call = Config::get()->serviceOperations();
        $this->assertNotNull($call);
    }

    public function test_getOperationsForPvWriteOff() {
        /**
         * Compose service.
         */
        /** @var  $mockCall Praxigento_Bonus_Service_Operations_Call */
        $mockCall = $this->mockCall(null);
        // TODO enable and complete test
        //        $resp = $mockCall->getOperationsForPvWriteOff();
        //        $this->assertTrue($resp instanceof Praxigento_Bonus_Service_Operations_Response_GetOperationsForPvWriteOff);
    }

    public function test_createOperationPvWriteOff() {
        $call = Config::get()->serviceOperations();
        $req  = $call->requestCreateOperationPvWriteOff();
        $req->setCustomerAccountId(3);
        $req->setPeriodCode('20150601');
        $req->setDateApplied('2015-06-01 23:59:59');
        $req->setValue(360);
        $resp = $call->createOperationPvWriteOff($req);
        // TODO enable and complete test
        //        $resp = $mockCall->getOperationsForPvWriteOff();
        //        $this->assertTrue($resp instanceof Praxigento_Bonus_Service_Operations_Response_GetOperationsForPvWriteOff);
    }

    public function test_updateBalance_accountExists() {
        $ACC_ID    = 321;
        $VAL_SAVED = 546;
        $VAL_INC   = 32;
        /**
         * Create mocks.
         */
        /* existing item  */
        $mockBalance = $this
            ->getMockBuilder('Praxigento_Bonus_Model_Own_Balance')
            ->setMethods(array( 'save', 'getData', 'setData' ))
            ->getMock();
        $mockBalance
            ->expects($this->at(0))
            ->method('getData')
            ->with($this->equalTo(Balance::ATTR_VALUE))
            ->will($this->returnValue($VAL_SAVED));
        $mockBalance
            ->expects($this->at(1))
            ->method('setData')
            ->with($this->equalTo(Balance::ATTR_VALUE), $this->equalTo($VAL_SAVED + $VAL_INC));
        $mockBalance
            ->expects($this->at(2))
            ->method('save');
        /* collectionBalance with found item */
        $mockCollection = $this
            ->getMockBuilder('Praxigento_Bonus_Resource_Own_Balance_Collection')
            ->setMethods(array( 'getSize', 'getFirstItem' ))
            ->getMock();
        $mockCollection
            ->expects($this->once())
            ->method('getSize')
            ->will($this->returnValue(1));
        $mockCollection
            ->expects($this->once())
            ->method('getFirstItem')
            ->will($this->returnValue($mockBalance));
        /* config */
        $mockCfg = $this
            ->getMockBuilder('Praxigento_Bonus_Config')
            ->setMethods(array( 'collectionBalance' ))
            ->getMock();
        $mockCfg
            ->expects($this->any())
            ->method('collectionBalance')
            ->will($this->returnValue($mockCollection));
        /* setup Config */
        Config::set($mockCfg);
        /**
         * Prepare request and perform call.
         */
        $call = Config::get()->serviceOperations();
        $req  = $call->requestUpdateBalance();
        $req->setAccountId($ACC_ID);
        $req->setValue($VAL_INC);
        $resp = $call->updateBalance($req);
        $this->assertTrue($resp instanceof Praxigento_Bonus_Service_Operations_Response_UpdateBalance);
        $this->assertTrue($resp->isSucceed());
    }

    public function test_updateBalance_accountNew() {
        $ACC_ID  = 321;
        $VAL_INC = 32;
        /**
         * Create mocks.
         */
        /* empty model */
        $mockBalance = $this
            ->getMockBuilder('Praxigento_Bonus_Model_Own_Balance')
            ->setMethods(array( 'save', 'getData', 'setData' ))
            ->getMock();
        $mockBalance
            ->expects($this->at(0))
            ->method('setData')
            ->with($this->equalTo(Balance::ATTR_ACCOUNT_ID), $this->equalTo($ACC_ID));
        $mockBalance
            ->expects($this->at(1))
            ->method('setData')
            ->with($this->equalTo(Balance::ATTR_PERIOD), $this->equalTo(Config::PERIOD_KEY_NOW));
        $mockBalance
            ->expects($this->at(2))
            ->method('getData')
            ->with($this->equalTo(Balance::ATTR_VALUE))
            ->will($this->returnValue(0));
        $mockBalance
            ->expects($this->at(3))
            ->method('setData')
            ->with($this->equalTo(Balance::ATTR_VALUE), $this->equalTo(0 + $VAL_INC));
        $mockBalance
            ->expects($this->at(4))
            ->method('save');
        /* collectionBalance with found item */
        $mockCollection = $this
            ->getMockBuilder('Praxigento_Bonus_Resource_Own_Balance_Collection')
            ->setMethods(array( 'getSize' ))
            ->getMock();
        $mockCollection
            ->expects($this->once())
            ->method('getSize')
            ->will($this->returnValue(0));
        /* config */
        $mockCfg = $this
            ->getMockBuilder('Praxigento_Bonus_Config')
            ->setMethods(array( 'collectionBalance', 'modelBalance' ))
            ->getMock();
        $mockCfg
            ->expects($this->any())
            ->method('collectionBalance')
            ->will($this->returnValue($mockCollection));
        $mockCfg
            ->expects($this->any())
            ->method('modelBalance')
            ->will($this->returnValue($mockBalance));
        /* setup Config */
        Config::set($mockCfg);
        /**
         * Prepare request and perform call.
         */
        $call = Config::get()->serviceOperations();
        $req  = $call->requestUpdateBalance();
        $req->setAccountId($ACC_ID);
        $req->setValue($VAL_INC);
        $resp = $call->updateBalance($req);
        $this->assertTrue($resp instanceof Praxigento_Bonus_Service_Operations_Response_UpdateBalance);
        $this->assertTrue($resp->isSucceed());
    }

    public function test_createTransaction() {
        $call = Config::get()->serviceOperations();
        $req  = $call->requestCreateOperationPvWriteOff();
        $req->setCustomerAccountId(3);
        $req->setPeriodCode('20150601');
        $req->setDateApplied('2015-06-01 23:59:59');
        $req->setValue(360);
        $resp = $call->createTransaction($req);
        // TODO enable and complete test
        //        $resp = $mockCall->getOperationsForPvWriteOff();
        //        $this->assertTrue($resp instanceof Praxigento_Bonus_Service_Operations_Response_GetOperationsForPvWriteOff);
    }


    /**
     * @param $methods array of methods to be mocked.
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function mockCall($methods) {
        $mockBuilder = $this->getMockBuilder('Praxigento_Bonus_Service_Operations_Call');
        $result      = $mockBuilder
            ->setMethods($methods)
            ->getMock();
        return $result;
    }

}