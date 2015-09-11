<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;
use Praxigento_Bonus_Model_Own_Log_Calc as LogCalc;
use Praxigento_Bonus_Service_Operations_Response_GetOperationsForPvWriteOff as GetOperationsForPvWriteOffResponse;
use Praxigento_Bonus_Service_Period_Response_GetPeriodForPvWriteOff as GetPeriodForPvWriteOffResponse;

include_once('../../../phpunit_bootstrap.php');

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Test_Service_Calculation_Hndl_WriteOffOperations_UnitTest
    extends PHPUnit_Framework_TestCase {

    /**
     * Reset Config before each test.
     */
    public function setUp() {
        Config::set(null);
    }

    public function test_constructor() {
        /**
         * Create mocks (direct order).
         */
        /* Config:: */
        $mockCfg = $this
            ->getMockBuilder('Praxigento_Bonus_Config')
            ->setMethods(array( 'helperAccount', 'helperPeriod' ))
            ->getMock();
        /* _helperAccount  */
        $mockHlpAccount = $this
            ->getMockBuilder('Praxigento_Bonus_Helper_Account')
            ->getMock();
        $mockCfg
            ->expects($this->once())
            ->method('helperAccount')
            ->will($this->returnValue($mockHlpAccount));
        /* _helperPeriod*/
        $mockHlpPeriod = $this
            ->getMockBuilder('Praxigento_Bonus_Helper_Period')
            ->getMock();
        $mockCfg
            ->expects($this->once())
            ->method('helperPeriod')
            ->will($this->returnValue($mockHlpPeriod));
        /**
         * Setup config and perform call.
         */
        Config::set($mockCfg);
        $hndl = new Praxigento_Bonus_Service_Calculation_Hndl_WriteOffOperations();
        $this->assertNotNull($hndl);
    }

    public function test_process() {
        $DATE_APPLIED = '2015-06-02 06:59:59';
        $ACCOUNTANT_ACC_ID = 1;
        /**
         * Create mocks (direct order).
         */
        /* Mock parameters */
        $PERIOD_VALUE = '20150903';
        $PERIOD_CODE = 'day';
        $mockParamReq = $this
            ->getMockBuilder('Praxigento_Bonus_Service_Operations_Response_GetOperationsForPvWriteOff')
            ->setMethods(array( 'getCollection' ))
            ->getMock();
        $mockCollection = new Varien_Data_Collection();
        $mockCollection->addItem(new Varien_Object(array(
            GetOperationsForPvWriteOffResponse::TRN_DEBIT_ACC_ID  => $ACCOUNTANT_ACC_ID,
            GetOperationsForPvWriteOffResponse::TRN_CREDIT_ACC_ID => 2,
            GetOperationsForPvWriteOffResponse::TRN_VALUE         => 200.00
        )));
        $mockCollection->addItem(new Varien_Object(array(
            GetOperationsForPvWriteOffResponse::TRN_DEBIT_ACC_ID  => $ACCOUNTANT_ACC_ID,
            GetOperationsForPvWriteOffResponse::TRN_CREDIT_ACC_ID => 3,
            GetOperationsForPvWriteOffResponse::TRN_VALUE         => 300.00
        )));
        $mockCollection->addItem(new Varien_Object(array(
            GetOperationsForPvWriteOffResponse::TRN_DEBIT_ACC_ID  => 3,
            GetOperationsForPvWriteOffResponse::TRN_CREDIT_ACC_ID => 2,
            GetOperationsForPvWriteOffResponse::TRN_VALUE         => 100.00
        )));
        $mockParamReq
            ->expects($this->once())
            ->method('getCollection')
            ->will($this->returnValue($mockCollection));
        /* Mock constructor related environment */
        /* Config:: */
        $mockCfg = $this
            ->getMockBuilder('Praxigento_Bonus_Config')
            ->setMethods(array( 'helperAccount', 'helperPeriod', 'serviceOperations' ))
            ->getMock();
        /* _helperAccount  */
        $mockHlpAccount = $this
            ->getMockBuilder('Praxigento_Bonus_Helper_Account')
            ->setMethods(array( 'getAccountantAccIdByAssetCode' ))
            ->getMock();
        $mockCfg
            ->expects($this->once())
            ->method('helperAccount')
            ->will($this->returnValue($mockHlpAccount));
        /* _helperPeriod*/
        $mockHlpPeriod = $this
            ->getMockBuilder('Praxigento_Bonus_Helper_Period')
            ->setMethods(array( 'calcPeriodTsTo' ))
            ->getMock();
        $mockCfg
            ->expects($this->once())
            ->method('helperPeriod')
            ->will($this->returnValue($mockHlpPeriod));
        /* Mock runtime environment */
        $mockHlpPeriod
            ->expects($this->once())
            ->method('calcPeriodTsTo')
            ->will($this->returnValue($DATE_APPLIED));
        $mockHlpAccount
            ->expects($this->once())
            ->method('getAccountantAccIdByAssetCode')
            ->with($this->equalTo(Config::ASSET_PV))
            ->will($this->returnValue($ACCOUNTANT_ACC_ID));
        $mockCallOp = $this
            ->getMockBuilder('Praxigento_Bonus_Service_Operations_Call')
            ->setMethods(array( 'requestCreateOperationPvWriteOff', 'createOperationPvWriteOff' ))
            ->getMock();
        $mockCfg
            ->expects($this->once())
            ->method('serviceOperations')
            ->will($this->returnValue($mockCallOp));
        $mockReqOp = $this
            ->getMockBuilder('Praxigento_Bonus_Service_Operations_Request_CreateOperationPvWriteOff')
            ->setMethods(array( 'requestCreateOperationPvWriteOff', 'createOperationPvWriteOff' ))
            ->getMock();
        $mockCallOp
            ->expects($this->any())
            ->method('requestCreateOperationPvWriteOff')
            ->will($this->returnValue($mockReqOp));
        $mockRespOp = $this
            ->getMockBuilder('Praxigento_Bonus_Service_Operations_Request_CreateOperationPvWriteOff')
            ->setMethods(array( 'isSucceed', 'getErrorCode' ))
            ->getMock();
        $mockCallOp
            ->expects($this->any())
            ->method('createOperationPvWriteOff')
            ->will($this->returnValue($mockRespOp));
        $mockRespOp
            ->expects($this->exactly(2))
            ->method('isSucceed')
            ->will($this->returnValue(true));
        /**
         * Setup config and perform call.
         */
        Config::set($mockCfg);
        $hndl = new Praxigento_Bonus_Service_Calculation_Hndl_WriteOffOperations();
        $hndl->process($mockParamReq, $PERIOD_VALUE, $PERIOD_CODE);
    }

    /**
     * @expectedException Mage_Core_Exception
     */
    public function test_exception() {
        $DATE_APPLIED = '2015-06-02 06:59:59';
        $ACCOUNTANT_ACC_ID = 1;
        $ERROR_CODE = 13;
        /**
         * Create mocks (direct order).
         */
        /* Mock parameters */
        $PERIOD_VALUE = '20150903';
        $PERIOD_CODE = 'day';
        $mockParamReq = $this
            ->getMockBuilder('Praxigento_Bonus_Service_Operations_Response_GetOperationsForPvWriteOff')
            ->setMethods(array( 'getCollection' ))
            ->getMock();
        $mockCollection = new Varien_Data_Collection();
        $mockCollection->addItem(new Varien_Object(array(
            GetOperationsForPvWriteOffResponse::TRN_DEBIT_ACC_ID  => $ACCOUNTANT_ACC_ID,
            GetOperationsForPvWriteOffResponse::TRN_CREDIT_ACC_ID => 2,
            GetOperationsForPvWriteOffResponse::TRN_VALUE         => 200.00
        )));
        $mockParamReq
            ->expects($this->once())
            ->method('getCollection')
            ->will($this->returnValue($mockCollection));
        /* Mock constructor related environment */
        /* Config:: */
        $mockCfg = $this
            ->getMockBuilder('Praxigento_Bonus_Config')
            ->setMethods(array( 'helperAccount', 'helperPeriod', 'serviceOperations' ))
            ->getMock();
        /* _helperAccount  */
        $mockHlpAccount = $this
            ->getMockBuilder('Praxigento_Bonus_Helper_Account')
            ->setMethods(array( 'getAccountantAccIdByAssetCode' ))
            ->getMock();
        $mockCfg
            ->expects($this->once())
            ->method('helperAccount')
            ->will($this->returnValue($mockHlpAccount));
        /* _helperPeriod*/
        $mockHlpPeriod = $this
            ->getMockBuilder('Praxigento_Bonus_Helper_Period')
            ->setMethods(array( 'calcPeriodTsTo' ))
            ->getMock();
        $mockCfg
            ->expects($this->once())
            ->method('helperPeriod')
            ->will($this->returnValue($mockHlpPeriod));
        /* Mock runtime environment */
        $mockHlpPeriod
            ->expects($this->once())
            ->method('calcPeriodTsTo')
            ->will($this->returnValue($DATE_APPLIED));
        $mockHlpAccount
            ->expects($this->once())
            ->method('getAccountantAccIdByAssetCode')
            ->with($this->equalTo(Config::ASSET_PV))
            ->will($this->returnValue($ACCOUNTANT_ACC_ID));
        $mockCallOp = $this
            ->getMockBuilder('Praxigento_Bonus_Service_Operations_Call')
            ->setMethods(array( 'requestCreateOperationPvWriteOff', 'createOperationPvWriteOff' ))
            ->getMock();
        $mockCfg
            ->expects($this->once())
            ->method('serviceOperations')
            ->will($this->returnValue($mockCallOp));
        $mockReqOp = $this
            ->getMockBuilder('Praxigento_Bonus_Service_Operations_Request_CreateOperationPvWriteOff')
            ->setMethods(array( 'requestCreateOperationPvWriteOff', 'createOperationPvWriteOff' ))
            ->getMock();
        $mockCallOp
            ->expects($this->any())
            ->method('requestCreateOperationPvWriteOff')
            ->will($this->returnValue($mockReqOp));
        $mockRespOp = $this
            ->getMockBuilder('Praxigento_Bonus_Service_Operations_Request_CreateOperationPvWriteOff')
            ->setMethods(array( 'isSucceed', 'getErrorCode' ))
            ->getMock();
        $mockCallOp
            ->expects($this->any())
            ->method('createOperationPvWriteOff')
            ->will($this->returnValue($mockRespOp));
        $mockRespOp
            ->expects($this->once())
            ->method('isSucceed')
            ->will($this->returnValue(false));
        $mockRespOp
            ->expects($this->once())
            ->method('getErrorCode')
            ->will($this->returnValue($ERROR_CODE));
        /**
         * Setup config and perform call.
         */
        Config::set($mockCfg);
        $hndl = new Praxigento_Bonus_Service_Calculation_Hndl_WriteOffOperations();
        $hndl->process($mockParamReq, $PERIOD_VALUE, $PERIOD_CODE);
    }

}