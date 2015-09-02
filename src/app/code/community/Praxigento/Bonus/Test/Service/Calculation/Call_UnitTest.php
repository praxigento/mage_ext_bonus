<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;
use Praxigento_Bonus_Service_Period_Response_GetPeriodForPvWriteOff as GetPeriodForPvWriteOffResponse;

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

    public function test_calcPvWriteOff_disabled() {
        /**
         * Create mocks.
         */
        /* helper Account  */
        $mockHlp = $this
            ->getMockBuilder('Praxigento_Bonus_Helper_Data')
            ->setMethods(array( 'cfgPersonalBonusEnabled' ))
            ->getMock();
        $mockHlp
            ->expects($this->any())
            ->method('cfgPersonalBonusEnabled')
            ->will($this->returnValue(false));
        /* logger */
        $mockLog = $this
            ->getMockBuilder('Praxigento_Bonus_Logger')
            ->setMethods(array( 'warn' ))
            ->getMock();
        $mockLog
            ->expects($this->once())
            ->method('warn')
            ->with($this->equalTo('Personal bonus is disabled. PV Write Off calculation cannot be started.'));
        /* config */
        $mockCfg = $this
            ->getMockBuilder('Praxigento_Bonus_Config')
            ->setMethods(array( 'helper', 'logger' ))
            ->getMock();
        $mockCfg
            ->expects($this->any())
            ->method('helper')
            ->will($this->returnValue($mockHlp));
        $mockCfg
            ->expects($this->any())
            ->method('logger')
            ->will($this->returnValue($mockLog));
        /* setup Config */
        Config::set($mockCfg);
        /**
         * Prepare request and perform call.
         */
        /** @var  $call Praxigento_Bonus_Service_Calculation_Call */
        $call = Config::get()->serviceCalculation();
        /** @var  $resp Praxigento_Bonus_Service_Calculation_Request_CalcPvWriteOff */
        $req = $call->requestCalcPvWriteOff();
        /** @var  $resp Praxigento_Bonus_Service_Calculation_Response_CalcPvWriteOff */
        $resp = $call->calcPvWriteOff($req);
        $this->assertFalse($resp->isSucceed());
    }

    public function test_calcPvWriteOff_error_noPeriod() {
        /**
         * Create mocks.
         */
        /* helper Account  */
        $mockHlp = $this
            ->getMockBuilder('Praxigento_Bonus_Helper_Data')
            ->setMethods(array( 'cfgPersonalBonusEnabled' ))
            ->getMock();
        $mockHlp
            ->expects($this->any())
            ->method('cfgPersonalBonusEnabled')
            ->will($this->returnValue(true));
        /* logger */
        $mockLog = $this
            ->getMockBuilder('Praxigento_Bonus_Logger')
            ->setMethods(array( 'error' ))
            ->getMock();
        $mockLog
            ->expects($this->once())
            ->method('error')
            ->with($this->equalTo('Cannot get period to calculate PV Write Off.'));
        /* servicePeriod response */
        $mockSrvPeriodResp = $this
            ->getMockBuilder('Praxigento_Bonus_Service_Period_Response_GetPeriodForPvWriteOff')
            ->setMethods(array( 'isSucceed', 'getErrorCode' ))
            ->getMock();
        $mockSrvPeriodResp
            ->expects($this->once())
            ->method('isSucceed')
            ->will($this->returnValue(false));
        $mockSrvPeriodResp
            ->expects($this->once())
            ->method('getErrorCode')
            ->will($this->returnValue(GetPeriodForPvWriteOffResponse::ERR_UNDEFINED));
        /* servicePeriod */
        $mockSrvPeriod = $this
            ->getMockBuilder('Praxigento_Bonus_Service_Period_Call')
            ->setMethods(array( 'getPeriodForPvWriteOff' ))
            ->getMock();
        $mockSrvPeriod
            ->expects($this->once())
            ->method('getPeriodForPvWriteOff')
            ->will($this->returnValue($mockSrvPeriodResp));
        /* config */
        $mockCfg = $this
            ->getMockBuilder('Praxigento_Bonus_Config')
            ->setMethods(array( 'helper', 'logger', 'servicePeriod' ))
            ->getMock();
        $mockCfg
            ->expects($this->any())
            ->method('helper')
            ->will($this->returnValue($mockHlp));
        $mockCfg
            ->expects($this->any())
            ->method('logger')
            ->will($this->returnValue($mockLog));
        $mockCfg
            ->expects($this->any())
            ->method('servicePeriod')
            ->will($this->returnValue($mockSrvPeriod));
        /* setup Config */
        Config::set($mockCfg);
        /**
         * Prepare request and perform call.
         */
        /** @var  $call Praxigento_Bonus_Service_Calculation_Call */
        $call = Config::get()->serviceCalculation();
        /** @var  $resp Praxigento_Bonus_Service_Calculation_Request_CalcPvWriteOff */
        $req = $call->requestCalcPvWriteOff();
        /** @var  $resp Praxigento_Bonus_Service_Calculation_Response_CalcPvWriteOff */
        $resp = $call->calcPvWriteOff($req);
        $this->assertFalse($resp->isSucceed());
    }

}