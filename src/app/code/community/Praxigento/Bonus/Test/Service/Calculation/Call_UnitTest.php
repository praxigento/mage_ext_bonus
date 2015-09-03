<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;
use Praxigento_Bonus_Model_Own_Log_Calc as LogCalc;
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

    public function test_calcPvWriteOff_process() {
        $PERIOD_VALUE     = '20150904';
        $PERIOD_TYPE_CODE = 123;
        $LOG_CALC_ID      = 654;
        /**
         * Create mocks (direct order).
         */
        /* config */
        $mockCfg = $this
            ->getMockBuilder('Praxigento_Bonus_Config')
            ->setMethods(array( 'helper', 'logger', 'servicePeriod', 'serviceOperations' ))
            ->getMock();
        /* helper  */
        $mockHlp = $this
            ->getMockBuilder('Praxigento_Bonus_Helper_Data')
            ->setMethods(array( 'cfgPersonalBonusEnabled' ))
            ->getMock();
        $mockCfg
            ->expects($this->any())
            ->method('helper')
            ->will($this->returnValue($mockHlp));
        $mockHlp
            ->expects($this->any())
            ->method('cfgPersonalBonusEnabled')
            ->will($this->returnValue(true));
        /* servicePeriod */
        $mockCall = $this
            ->getMockBuilder('Praxigento_Bonus_Service_Period_Call')
            ->setMethods(array( 'getPeriodForPvWriteOff', 'requestRegisterPeriodCalculation', 'registerPeriodCalculation' ))
            ->getMock();
        $mockCfg
            ->expects($this->any())
            ->method('servicePeriod')
            ->will($this->returnValue($mockCall));
        /* servicePeriod response */
        $mockResp = $this
            ->getMockBuilder('Praxigento_Bonus_Service_Period_Response_GetPeriodForPvWriteOff')
            ->setMethods(array( 'isSucceed', 'getPeriodValue', 'getPeriodTypeCode' ))
            ->getMock();
        $mockCall
            ->expects($this->once())
            ->method('getPeriodForPvWriteOff')
            ->will($this->returnValue($mockResp));
        $mockResp
            ->expects($this->once())
            ->method('isSucceed')
            ->will($this->returnValue(true));
        $mockResp
            ->expects($this->once())
            ->method('getPeriodValue')
            ->will($this->returnValue($PERIOD_VALUE));
        $mockResp
            ->expects($this->once())
            ->method('getPeriodTypeCode')
            ->will($this->returnValue($PERIOD_TYPE_CODE));
        /* servicePeriod requestRegisterPeriodCalculation */
        $mockReqR = $this
            ->getMockBuilder('Praxigento_Bonus_Service_Period_Request_RegisterPeriodCalculation')
            ->getMock();
        $mockCall
            ->expects($this->once())
            ->method('requestRegisterPeriodCalculation')
            ->will($this->returnValue($mockReqR));
        /* service period:  RegisterPeriodCalculationResponse */
        $mockRespR = $this
            ->getMockBuilder('Praxigento_Bonus_Service_Period_Request_RegisterPeriodCalculation')
            ->setMethods(array( 'getPeriod', 'getLogCalc' ))
            ->getMock();
        $mockCall
            ->expects($this->once())
            ->method('registerPeriodCalculation')
            ->will($this->returnValue($mockRespR));
        /* logCalc model in service operations response GetOperationsForPvWriteOff */
        $mockLogCalc = $this
            ->getMockBuilder('Praxigento_Bonus_Model_Own_Log_Calc')
            ->setMethods(array( 'getId', 'setData', 'getResource' ))
            ->getMock();
        $mockRespR
            ->expects($this->once())
            ->method('getLogCalc')
            ->will($this->returnValue($mockLogCalc));
        $mockLogCalc
            ->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($LOG_CALC_ID));

        /* service operations */
        $mockCallOp = $this
            ->getMockBuilder('Praxigento_Bonus_Service_Operations_Call')
            ->setMethods(array( 'requestGetOperationsForPvWriteOff', 'getOperationsForPvWriteOff' ))
            ->getMock();
        $mockCfg
            ->expects($this->any())
            ->method('serviceOperations')
            ->will($this->returnValue($mockCallOp));
        /* service operations: request GetOperationsForPvWriteOff */
        $mockReqOpGet = $this
            ->getMockBuilder('Praxigento_Bonus_Service_Operations_Request_GetOperationsForPvWriteOff')
            ->setMethods(array( 'requestGetOperationsForPvWriteOff', 'getOperationsForPvWriteOff' ))
            ->getMock();
        $mockCallOp
            ->expects($this->once())
            ->method('requestGetOperationsForPvWriteOff')
            ->will($this->returnValue($mockReqOpGet));
        /* service operations: response GetOperationsForPvWriteOff */
        $mockRespOpGet = $this
            ->getMockBuilder('Praxigento_Bonus_Service_Operations_Response_GetOperationsForPvWriteOff')
            ->setMethods(array( 'getLogCalc', 'getCollection' ))
            ->getMock();
        $mockCallOp
            ->expects($this->once())
            ->method('getOperationsForPvWriteOff')
            ->will($this->returnValue($mockRespOpGet));
        $mockRespOpGet
            ->expects($this->once())
            ->method('getCollection')
            ->will($this->returnValue(new Praxigento_Bonus_Resource_Own_Operation_Collection()));
        /* log calculation complete */
        $mockLogCalc
            ->expects($this->once())
            ->method('setData')
            ->with(
                $this->equalTo(LogCalc::ATTR_STATE),
                $this->equalTo(Config::STATE_PERIOD_COMPLETE)
            );
        /* logCalc resource*/
        $mockLogCalcRsrc = $this
            ->getMockBuilder('Praxigento_Bonus_Resource_Own_Log_Calc')
            ->setMethods(array( 'save' ))
            ->getMock();
        $mockLogCalc
            ->expects($this->any())
            ->method('getResource')
            ->will($this->returnValue($mockLogCalcRsrc));
        $mockLogCalcRsrc
            ->expects($this->once())
            ->method('save');

        /* logger */
        $mockLogger = $this
            ->getMockBuilder('Praxigento_Bonus_Logger')
            ->getMock();
        $mockCfg
            ->expects($this->any())
            ->method('logger')
            ->will($this->returnValue($mockLogger));
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
        $this->assertTrue($resp->isSucceed());
    }

    public function test_calcPvWriteOff_nothingToDo_oldPeriod() {
        /**
         * Create mocks.
         */
        /* helper */
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
            ->setMethods(array( 'warn' ))
            ->getMock();
        $mockLog
            ->expects($this->once())
            ->method('warn')
            ->with($this->equalTo('There are no operations to calculate PV Write Off.'));
        /* servicePeriod response */
        $mockSrvPeriodResp = $this
            ->getMockBuilder('Praxigento_Bonus_Service_Period_Response_GetPeriodForPvWriteOff')
            ->setMethods(array( 'isSucceed', 'getErrorCode', 'isNewPeriod' ))
            ->getMock();
        $mockSrvPeriodResp
            ->expects($this->once())
            ->method('isSucceed')
            ->will($this->returnValue(false));
        $mockSrvPeriodResp
            ->expects($this->once())
            ->method('getErrorCode')
            ->will($this->returnValue(GetPeriodForPvWriteOffResponse::ERR_NOTHING_TO_DO));
        $mockSrvPeriodResp
            ->expects($this->once())
            ->method('isNewPeriod')
            ->will($this->returnValue(false));
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
        $this->assertTrue($resp->isSucceed());
    }

    public function test_calcPvWriteOff_nothingToDo_newPeriod() {
        $PERIOD_VAL = '20150902';
        /**
         * Create mocks (direct order).
         */
        /* config */
        $mockCfg = $this
            ->getMockBuilder('Praxigento_Bonus_Config')
            ->setMethods(array( 'helper', 'logger', 'servicePeriod' ))
            ->getMock();
        /* helper  */
        $mockHlp = $this
            ->getMockBuilder('Praxigento_Bonus_Helper_Data')
            ->setMethods(array( 'cfgPersonalBonusEnabled' ))
            ->getMock();
        $mockCfg
            ->expects($this->any())
            ->method('helper')
            ->will($this->returnValue($mockHlp));
        $mockHlp
            ->expects($this->any())
            ->method('cfgPersonalBonusEnabled')
            ->will($this->returnValue(true));
        /* servicePeriod */
        $mockSrvPeriod = $this
            ->getMockBuilder('Praxigento_Bonus_Service_Period_Call')
            ->setMethods(array( 'getPeriodForPvWriteOff', 'requestRegisterPeriodCalculation', 'registerPeriodCalculation' ))
            ->getMock();
        $mockCfg
            ->expects($this->any())
            ->method('servicePeriod')
            ->will($this->returnValue($mockSrvPeriod));
        /* servicePeriod response */
        $mockSrvPeriodResp = $this
            ->getMockBuilder('Praxigento_Bonus_Service_Period_Response_GetPeriodForPvWriteOff')
            ->setMethods(array( 'isSucceed', 'getErrorCode', 'isNewPeriod', 'getPeriodValue' ))
            ->getMock();
        $mockSrvPeriod
            ->expects($this->once())
            ->method('getPeriodForPvWriteOff')
            ->will($this->returnValue($mockSrvPeriodResp));
        $mockSrvPeriodResp
            ->expects($this->once())
            ->method('isSucceed')
            ->will($this->returnValue(false));
        $mockSrvPeriodResp
            ->expects($this->once())
            ->method('getErrorCode')
            ->will($this->returnValue(GetPeriodForPvWriteOffResponse::ERR_NOTHING_TO_DO));
        $mockSrvPeriodResp
            ->expects($this->once())
            ->method('isNewPeriod')
            ->will($this->returnValue(true));
        $mockSrvPeriodResp
            ->expects($this->once())
            ->method('getPeriodValue')
            ->will($this->returnValue($PERIOD_VAL));
        /* servicePeriod requestRegisterPeriodCalculation */
        $mockSrvPeriodReqR = $this
            ->getMockBuilder('Praxigento_Bonus_Service_Period_Request_RegisterPeriodCalculation')
            ->getMock();
        $mockSrvPeriod
            ->expects($this->once())
            ->method('requestRegisterPeriodCalculation')
            ->will($this->returnValue($mockSrvPeriodReqR));
        /* servicePeriod response registerPeriodCalculation */
        $mockSrvPeriodRespR = $this
            ->getMockBuilder('Praxigento_Bonus_Service_Period_Response_RegisterPeriodCalculation')
            ->setMethods(array( 'getLogCalc' ))
            ->getMock();
        $mockSrvPeriod
            ->expects($this->once())
            ->method('registerPeriodCalculation')
            ->will($this->returnValue($mockSrvPeriodRespR));
        /* logCalc model from registerPeriodCalculation response */
        $mockLogCalc = $this
            ->getMockBuilder('Praxigento_Bonus_Model_Own_Log_Calc')
            ->setMethods(array( 'setData', 'getResource' ))
            ->getMock();
        $mockSrvPeriodRespR
            ->expects($this->once())
            ->method('getLogCalc')
            ->will($this->returnValue($mockLogCalc));
        $mockLogCalc
            ->expects($this->any())
            ->method('setData')
            ->with($this->equalTo(LogCalc::ATTR_STATE), $this->equalTo(Config::STATE_PERIOD_COMPLETE));
        /* logCalc resource*/
        $mockLogCalcRsrc = $this
            ->getMockBuilder('Praxigento_Bonus_Resource_Own_Log_Calc')
            ->setMethods(array( 'save' ))
            ->getMock();
        $mockLogCalc
            ->expects($this->any())
            ->method('getResource')
            ->will($this->returnValue($mockLogCalcRsrc));
        $mockLogCalcRsrc
            ->expects($this->once())
            ->method('save');
        /* logger */
        $mockLogger = $this
            ->getMockBuilder('Praxigento_Bonus_Logger')
            ->getMock();
        $mockCfg
            ->expects($this->any())
            ->method('logger')
            ->will($this->returnValue($mockLogger));
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
        $this->assertTrue($resp->isSucceed());
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