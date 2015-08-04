<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;
use Praxigento_Bonus_Model_Own_Period as Period;

include_once('../../phpunit_bootstrap.php');

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Test_Service_Period_Call_UnitTest extends PHPUnit_Framework_TestCase
{

    public function test_constructor()
    {
        $call = Mage::getModel(Config::CFG_SERVICE . '/period_call');
        $this->assertNotNull($call);
    }

    public function test_getPeriodForPersonalBonus()
    {
        /* add mocks */
        $mockBuilder = $this->getMockBuilder('Praxigento_Bonus_Resource_Own_Period_Collection');
        $mockPeriodColl = $mockBuilder
            ->disableOriginalConstructor()
            ->getMock();
        $mockBuilder = $this->getMockBuilder('Praxigento_Bonus_Resource_Own_Transaction_Collection');
        $mockTransactionColl = $mockBuilder
            ->disableOriginalConstructor()
            ->getMock();
        /* compose service */
        $mockBuilder = $this->getMockBuilder('Praxigento_Bonus_Service_Period_Call');
        $mockCall = $mockBuilder
            ->setMethods(array('getPeriodCollection', 'getTransactionCollection'))
            ->getMock();
        $mockCall->expects($this->any())->method('getPeriodCollection')->will($this->returnValue($mockPeriodColl));
        $mockCall->expects($this->any())->method('getTransactionCollection')->will($this->returnValue($mockTransactionColl));
        /** @var  $req  Praxigento_Bonus_Service_Period_Request_GetPeriodForPersonalBonus */
        $req = Mage::getModel(Config::CFG_SERVICE . '/period_request_getPeriodForPersonalBonus');
        $req->bonusTypeId = 1;
        $req->operationTypeIds = array(1, 2, 3);
        $req->periodCode = 'DAY';
        $req->periodTypeId = 3;
//        $resp = $mockCall->getPeriodForPersonalBonus($req);
        /** @var  $resp Praxigento_Bonus_Service_Period_Response_GetPeriodForPersonalBonus */
        $call = Mage::getModel(Config::CFG_SERVICE . '/period_call');
        $resp = $call->getPeriodForPersonalBonus($req);
        $this->assertFalse($resp->isSucceed());

    }

    /**
     * Get value for period in state 'processing'.
     */
    public function test_getPeriodForPersonalBonus_processing()
    {
        /* test data */
        $bonusTypeId = 1;
        $periodTypeId = 2;
        $periodCode = Config::PERIOD_WEEK;
        $opType1 = 121;
        $opType2 = 232;
        $opType3 = 343;
        $opTypes = array($opType1, $opType2, $opType3);
        $periodValue = '20150804';
        /* add mocks */
        $mockBuilder = $this->getMockBuilder('Praxigento_Bonus_Resource_Own_Period_Collection');
        $mockPeriodColl = $mockBuilder
            ->disableOriginalConstructor()
            ->getMock();
        $mockPeriodColl->expects($this->exactly(3))
            ->method('addFieldToFilter')
            ->withConsecutive(
                array(Period::ATTR_BONUS_ID, $bonusTypeId),
                array(Period::ATTR_TYPE, $periodTypeId),
                array(Period::ATTR_STATE, Config::STATE_PERIOD_PROCESSING)
            );
        $mockPeriodColl->expects($this->once())
            ->method('getSize')->will($this->returnValue(1));
        /* item found */
        /** @var  $item Praxigento_Bonus_Model_Own_Period */
        $item = Mage::getModel('prxgt_bonus_model/period');
        $item->setValue($periodValue);
        $mockPeriodColl->expects($this->once())
            ->method('getFirstItem')->will($this->returnValue($item));
        /* compose service */
        $mockBuilder = $this->getMockBuilder('Praxigento_Bonus_Service_Period_Call');
        $mockCall = $mockBuilder
            ->setMethods(array('getPeriodCollection', 'getTransactionCollection'))
            ->getMock();
        $mockCall->expects($this->any())->method('getPeriodCollection')->will($this->returnValue($mockPeriodColl));
        /** @var  $req  Praxigento_Bonus_Service_Period_Request_GetPeriodForPersonalBonus */
        $req = Mage::getModel(Config::CFG_SERVICE . '/period_request_getPeriodForPersonalBonus');
        $req->bonusTypeId = $bonusTypeId;
        $req->operationTypeIds = $opTypes;
        $req->periodCode = $periodCode;
        $req->periodTypeId = $periodTypeId;
        /** @var  $resp Praxigento_Bonus_Service_Period_Response_GetPeriodForPersonalBonus */
        $resp = $mockCall->getPeriodForPersonalBonus($req);
        $this->assertTrue($resp->isSucceed());
        $this->assertEquals($periodValue, $resp->periodValue);
    }
}