<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;
use Praxigento_Bonus_Model_Own_Balance as Balance;
use Praxigento_Bonus_Service_Operations_Response_CreateOperationPvWriteOff as CreateOperationPvWriteOffResponse;

include_once('../../phpunit_bootstrap.php');

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Test_Service_Operations_Call_UnitTest
    extends PHPUnit_Framework_TestCase {

    /**
     * Reset Config before each test.
     */
    public function setUp() {
        Config::set(null);
    }

    public function test_constructor() {
        /** @var  $call Praxigento_Bonus_Service_Operations_Call */
        $call = Config::get()->serviceOperations();
        $this->assertNotNull($call);
    }

    public function test_getOperationsForPvWriteOff() {
        /**
         * Create mocks.
         */
        /* select to join other tables */
        $mockSelect = $this
            ->getMockBuilder('Varien_Db_Select')
            ->getMock();
        /* collection */
        $mockCollection = $this
            ->getMockBuilder('Praxigento_Bonus_Resource_Own_Operation_Collection')
            ->disableOriginalConstructor()
            ->setMethods(array( 'getSelect', 'getSelectSql', 'addFieldToFilter', 'getTable' ))
            ->getMock();
        $mockCollection
            ->expects($this->any())
            ->method('getSelect')
            ->will($this->returnValue($mockSelect));
        /* helper Types */
        $mockHlpType = $this
            ->getMockBuilder('Praxigento_Bonus_Helper_Type')
            ->getMock();
        $mockHlpType
            ->expects($this->any())
            ->method('getOperIdsForPvWriteOff')
            ->will($this->returnValue(array( 1 )));
        /* helper Period */
        $mockHlpPeriod = $this
            ->getMockBuilder('Praxigento_Bonus_Helper_Period')
            ->getMock();
        /* config */
        $mockCfg = $this
            ->getMockBuilder('Praxigento_Bonus_Config')
            ->setMethods(array( 'collectionOperation', 'helperType', 'helperPeriod' ))
            ->getMock();
        $mockCfg
            ->expects($this->any())
            ->method('collectionOperation')
            ->will($this->returnValue($mockCollection));
        $mockCfg
            ->expects($this->any())
            ->method('helperType')
            ->will($this->returnValue($mockHlpType));
        $mockCfg
            ->expects($this->any())
            ->method('helperPeriod')
            ->will($this->returnValue($mockHlpPeriod));
        /* setup Config */
        Config::set($mockCfg);
        /**
         * Prepare request and perform call.
         */
        $call = Config::get()->serviceOperations();
        $req  = $call->requestGetOperationsForPvWriteOff();
        $req->setPeriodCode(Config::PERIOD_DAY);
        $req->setPeriodValue('20150601');
        $resp = $call->getOperationsForPvWriteOff($req);
        $this->assertTrue($resp instanceof Praxigento_Bonus_Service_Operations_Response_GetOperationsForPvWriteOff);
        $this->assertTrue($resp->isSucceed());
    }

    public function test_createOperationPvWriteOff_commit_zero() {
        $CUST_ACC_ID  = 543;
        $STORE_ACC_ID = 345;
        $OPER_ID      = 546;
        $DATE_APPLIED = '2015-08-12 12:23:34';
        $VALUE        = 0;
        /**
         * Create mocks.
         */
        /* connection */
        $mockConn = $this
            ->getMockBuilder('Varien_Db_Adapter_Pdo_Mysql')
            ->disableOriginalConstructor()
            ->setMethods(array( 'beginTransaction', 'commit' ))
            ->getMock();
        $mockConn
            ->expects($this->once())
            ->method('beginTransaction');
        $mockConn
            ->expects($this->once())
            ->method('commit');
        /* accountant acc */
        $mockAcc = $this
            ->getMockBuilder('Praxigento_Bonus_Model_Own_Account')
            ->getMock();
        $mockAcc
            ->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($STORE_ACC_ID));
        /* helper Account  */
        $mockHlpAccount = $this
            ->getMockBuilder('Praxigento_Bonus_Helper_Account')
            ->getMock();
        $mockHlpAccount
            ->expects($this->any())
            ->method('getAccountantAccByAssetCode')
            ->will($this->returnValue($mockAcc));
        /* helper Types */
        $mockHlpType = $this
            ->getMockBuilder('Praxigento_Bonus_Helper_Type')
            ->getMock();
        $mockHlpType
            ->expects($this->any())
            ->method('getOperId')
            ->will($this->returnValue($OPER_ID));
        /* operation to create */
        $mockOper         = $this
            ->getMockBuilder('Praxigento_Bonus_Model_Own_Operation')
            ->setMethods(array( 'getResource' ))
            ->getMock();
        $mockOperResource = $this
            ->getMockBuilder('Praxigento_Bonus_Resource_Own_Operation')
            ->setMethods(array( 'save' ))
            ->getMock();
        $mockOper
            ->expects($this->any())
            ->method('getResource')
            ->will($this->returnValue($mockOperResource));
        $mockOperResource
            ->expects($this->once())
            ->method('save');
        /* config */
        $mockCfg = $this
            ->getMockBuilder('Praxigento_Bonus_Config')
            ->setMethods(array( 'connectionWrite', 'helperAccount', 'helperType', 'modelOperation' ))
            ->getMock();
        $mockCfg
            ->expects($this->any())
            ->method('connectionWrite')
            ->will($this->returnValue($mockConn));
        $mockCfg
            ->expects($this->any())
            ->method('helperAccount')
            ->will($this->returnValue($mockHlpAccount));
        $mockCfg
            ->expects($this->any())
            ->method('helperType')
            ->will($this->returnValue($mockHlpType));
        $mockCfg
            ->expects($this->any())
            ->method('modelOperation')
            ->will($this->returnValue($mockOper));
        /* setup Config */
        Config::set($mockCfg);
        /**
         * Service call mock.
         */
        /* call */
        $mockCall = $this
            ->getMockBuilder('Praxigento_Bonus_Service_Operations_Call')
            ->setMethods(array( 'createTransaction' ))
            ->getMock();
        /**
         * Prepare request and perform call.
         */
        $req = $mockCall->requestCreateOperationPvWriteOff();
        $req->setCustomerAccountId($CUST_ACC_ID);
        $req->setDateApplied($DATE_APPLIED);
        $req->setValue($VALUE);
        $resp = $mockCall->createOperationPvWriteOff($req);
        $this->assertTrue($resp instanceof Praxigento_Bonus_Service_Operations_Response_CreateOperationPvWriteOff);
        $this->assertTrue($resp->isSucceed());
    }

    public function test_createOperationPvWriteOff_commit_notZero() {
        $CUST_ACC_ID  = 543;
        $STORE_ACC_ID = 345;
        $OPER_ID      = 546;
        $DATE_APPLIED = '2015-08-12 12:23:34';
        $VALUE        = 43.92;
        /**
         * Create mocks.
         */
        /* connection */
        $mockConn = $this
            ->getMockBuilder('Varien_Db_Adapter_Pdo_Mysql')
            ->disableOriginalConstructor()
            ->setMethods(array( 'beginTransaction', 'commit' ))
            ->getMock();
        $mockConn
            ->expects($this->once())
            ->method('beginTransaction');
        $mockConn
            ->expects($this->once())
            ->method('commit');
        /* accountant acc */
        $mockAcc = $this
            ->getMockBuilder('Praxigento_Bonus_Model_Own_Account')
            ->getMock();
        $mockAcc
            ->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($STORE_ACC_ID));
        /* helper Account  */
        $mockHlpAccount = $this
            ->getMockBuilder('Praxigento_Bonus_Helper_Account')
            ->getMock();
        $mockHlpAccount
            ->expects($this->any())
            ->method('getAccountantAccByAssetCode')
            ->will($this->returnValue($mockAcc));
        /* helper Types */
        $mockHlpType = $this
            ->getMockBuilder('Praxigento_Bonus_Helper_Type')
            ->getMock();
        $mockHlpType
            ->expects($this->any())
            ->method('getOperId')
            ->will($this->returnValue($OPER_ID));
        /* operation to create */
        $mockOper         = $this
            ->getMockBuilder('Praxigento_Bonus_Model_Own_Operation')
            ->setMethods(array( 'getResource' ))
            ->getMock();
        $mockOperResource = $this
            ->getMockBuilder('Praxigento_Bonus_Resource_Own_Operation')
            ->setMethods(array( 'save' ))
            ->getMock();
        $mockOper
            ->expects($this->once())
            ->method('getResource')
            ->will($this->returnValue($mockOperResource));
        $mockOperResource
            ->expects($this->once())
            ->method('save');
        /* config */
        $mockCfg = $this
            ->getMockBuilder('Praxigento_Bonus_Config')
            ->setMethods(array( 'connectionWrite', 'helperAccount', 'helperType', 'modelOperation' ))
            ->getMock();
        $mockCfg
            ->expects($this->any())
            ->method('connectionWrite')
            ->will($this->returnValue($mockConn));
        $mockCfg
            ->expects($this->any())
            ->method('helperAccount')
            ->will($this->returnValue($mockHlpAccount));
        $mockCfg
            ->expects($this->any())
            ->method('helperType')
            ->will($this->returnValue($mockHlpType));
        $mockCfg
            ->expects($this->any())
            ->method('modelOperation')
            ->will($this->returnValue($mockOper));
        /* setup Config */
        Config::set($mockCfg);
        /**
         * Service call mock.
         */
        /* response: create transaction */
        $mockRestTrn = $this
            ->getMockBuilder('Praxigento_Bonus_Service_Operations_Response_CreateTransaction')
            ->setMethods(array( 'isSucceed' ))
            ->getMock();
        $mockRestTrn
            ->expects($this->once())
            ->method('isSucceed')
            ->will($this->returnValue(true));
        /* call */
        $mockCall = $this
            ->getMockBuilder('Praxigento_Bonus_Service_Operations_Call')
            ->setMethods(array( 'createTransaction' ))
            ->getMock();
        $mockCall
            ->expects($this->at(0))
            ->method('createTransaction')
            ->will($this->returnValue($mockRestTrn));
        /**
         * Prepare request and perform call.
         */
        $req = $mockCall->requestCreateOperationPvWriteOff();
        $req->setCustomerAccountId($CUST_ACC_ID);
        $req->setDateApplied($DATE_APPLIED);
        $req->setValue($VALUE);
        $resp = $mockCall->createOperationPvWriteOff($req);
        $this->assertTrue($resp instanceof Praxigento_Bonus_Service_Operations_Response_CreateOperationPvWriteOff);
        $this->assertTrue($resp->isSucceed());
    }

    public function test_createOperationPvWriteOff_rollback() {
        $CUST_ACC_ID  = 543;
        $STORE_ACC_ID = 345;
        $OPER_ID      = 546;
        $DATE_APPLIED = '2015-08-12 12:23:34';
        $VALUE        = 43.92;
        /**
         * Create mocks.
         */
        /* connection */
        $mockConn = $this
            ->getMockBuilder('Varien_Db_Adapter_Pdo_Mysql')
            ->disableOriginalConstructor()
            ->setMethods(array( 'beginTransaction', 'rollBack' ))
            ->getMock();
        $mockConn
            ->expects($this->once())
            ->method('beginTransaction');
        $mockConn
            ->expects($this->once())
            ->method('rollBack');
        /* accountant acc */
        $mockAcc = $this
            ->getMockBuilder('Praxigento_Bonus_Model_Own_Account')
            ->getMock();
        $mockAcc
            ->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($STORE_ACC_ID));
        /* helper Account  */
        $mockHlpAccount = $this
            ->getMockBuilder('Praxigento_Bonus_Helper_Account')
            ->getMock();
        $mockHlpAccount
            ->expects($this->any())
            ->method('getAccountantAccByAssetCode')
            ->will($this->returnValue($mockAcc));
        /* helper Types */
        $mockHlpType = $this
            ->getMockBuilder('Praxigento_Bonus_Helper_Type')
            ->getMock();
        $mockHlpType
            ->expects($this->any())
            ->method('getOperId')
            ->will($this->returnValue($OPER_ID));
        /* operation to create */
        $mockOper         = $this
            ->getMockBuilder('Praxigento_Bonus_Model_Own_Operation')
            ->setMethods(array( 'getResource' ))
            ->getMock();
        $mockOperResource = $this
            ->getMockBuilder('Praxigento_Bonus_Resource_Own_Operation')
            ->setMethods(array( 'save' ))
            ->getMock();
        $mockOper
            ->expects($this->once())
            ->method('getResource')
            ->will($this->returnValue($mockOperResource));
        $mockOperResource
            ->expects($this->once())
            ->method('save');
        /* config */
        $mockCfg = $this
            ->getMockBuilder('Praxigento_Bonus_Config')
            ->setMethods(array( 'connectionWrite', 'helperAccount', 'helperType', 'modelOperation' ))
            ->getMock();
        $mockCfg
            ->expects($this->any())
            ->method('connectionWrite')
            ->will($this->returnValue($mockConn));
        $mockCfg
            ->expects($this->any())
            ->method('helperAccount')
            ->will($this->returnValue($mockHlpAccount));
        $mockCfg
            ->expects($this->any())
            ->method('helperType')
            ->will($this->returnValue($mockHlpType));
        $mockCfg
            ->expects($this->any())
            ->method('modelOperation')
            ->will($this->returnValue($mockOper));
        /* setup Config */
        Config::set($mockCfg);
        /**
         * Service call mock.
         */
        /* response: create transaction */
        $mockRestTrn = $this
            ->getMockBuilder('Praxigento_Bonus_Service_Operations_Response_CreateTransaction')
            ->setMethods(array( 'isSucceed', 'getErrorMessage' ))
            ->getMock();
        $mockRestTrn
            ->expects($this->once())
            ->method('isSucceed')
            ->will($this->returnValue(false));
        $mockRestTrn
            ->expects($this->once())
            ->method('getErrorMessage')
            ->will($this->returnValue('This is mocked error.'));
        /* call */
        $mockCall = $this
            ->getMockBuilder('Praxigento_Bonus_Service_Operations_Call')
            ->setMethods(array( 'createTransaction' ))
            ->getMock();
        $mockCall
            ->expects($this->at(0))
            ->method('createTransaction')
            ->will($this->returnValue($mockRestTrn));
        /**
         * Prepare request and perform call.
         */
        $req = $mockCall->requestCreateOperationPvWriteOff();
        $req->setCustomerAccountId($CUST_ACC_ID);
        $req->setDateApplied($DATE_APPLIED);
        $req->setValue($VALUE);
        /** @var  $resp Praxigento_Bonus_Service_Operations_Response_CreateOperationPvWriteOff */
        $resp = $mockCall->createOperationPvWriteOff($req);
        $this->assertTrue($resp instanceof Praxigento_Bonus_Service_Operations_Response_CreateOperationPvWriteOff);
        $this->assertFalse($resp->isSucceed());
        $this->assertEquals(CreateOperationPvWriteOffResponse::ERR_FAILED, $resp->getErrorCode());
        $this->assertNotNull($resp->getErrorMessage());
    }

    public function test_createTransaction_commit() {
        $DEBIT_ACC_ID  = 321;
        $CREDIT_ACC_ID = 789;
        $VALUE         = 654.98;
        $OPER_ID       = 587;
        $DATE_APPLIED  = '2015-09-01 12:43:54';
        /**
         * Create mocks.
         */
        /* connection */
        $mockConn = $this
            ->getMockBuilder('Varien_Db_Adapter_Pdo_Mysql')
            ->disableOriginalConstructor()
            ->setMethods(array( 'beginTransaction', 'commit' ))
            ->getMock();
        $mockConn
            ->expects($this->once())
            ->method('beginTransaction');
        $mockConn
            ->expects($this->once())
            ->method('commit');
        /* transaction model */
        $mockTrans         = $this
            ->getMockBuilder('Praxigento_Bonus_Model_Own_Transaction')
            ->setMethods(array( 'getResource' ))
            ->getMock();
        $mockTransResource = $this
            ->getMockBuilder('Praxigento_Bonus_Resource_Own_Transaction')
            ->setMethods(array( 'save' ))
            ->getMock();
        $mockTrans
            ->expects($this->once())
            ->method('getResource')
            ->will($this->returnValue($mockTransResource));
        $mockTransResource
            ->expects($this->once())
            ->method('save');
        /* config */
        $mockCfg = $this
            ->getMockBuilder('Praxigento_Bonus_Config')
            ->setMethods(array( 'connectionWrite', 'modelTransaction' ))
            ->getMock();
        $mockCfg
            ->expects($this->any())
            ->method('connectionWrite')
            ->will($this->returnValue($mockConn));
        $mockCfg
            ->expects($this->any())
            ->method('modelTransaction')
            ->will($this->returnValue($mockTrans));
        /* setup Config */
        Config::set($mockCfg);
        /** service call mock */
        $mockCall = $this
            ->getMockBuilder('Praxigento_Bonus_Service_Operations_Call')
            ->setMethods(array( 'updateBalance' ))
            ->getMock();
        $mockCall
            ->expects($this->at(0))
            ->method('updateBalalnce');
        $mockCall
            ->expects($this->at(1))
            ->method('updateBalalnce');
        /**
         * Prepare request and perform call.
         */
        $req = $mockCall->requestCreateTransaction();
        $req->setCreditAccId($CREDIT_ACC_ID);
        $req->setDebitAccId($DEBIT_ACC_ID);
        $req->setValue($VALUE);
        $req->setOperationId($OPER_ID);
        $req->setDateApplied($DATE_APPLIED);
        $resp = $mockCall->createTransaction($req);
        $this->assertTrue($resp instanceof Praxigento_Bonus_Service_Operations_Response_CreateTransaction);
        $this->assertTrue($resp->isSucceed());
    }

    public function test_createTransaction_rollback() {
        $DEBIT_ACC_ID  = 321;
        $CREDIT_ACC_ID = 789;
        $VALUE         = 654.98;
        $OPER_ID       = 587;
        $DATE_APPLIED  = '2015-09-01 12:43:54';
        /**
         * Create mocks.
         */
        /* connection */
        $mockConn = $this
            ->getMockBuilder('Varien_Db_Adapter_Pdo_Mysql')
            ->disableOriginalConstructor()
            ->setMethods(array( 'beginTransaction', 'rollBack' ))
            ->getMock();
        $mockConn
            ->expects($this->once())
            ->method('beginTransaction');
        $mockConn
            ->expects($this->once())
            ->method('rollBack');
        /* transaction model */
        $mockTrans         = $this
            ->getMockBuilder('Praxigento_Bonus_Model_Own_Transaction')
            ->setMethods(array( 'getResource' ))
            ->getMock();
        $mockTransResource = $this
            ->getMockBuilder('Praxigento_Bonus_Resource_Own_Transaction')
            ->setMethods(array( 'save' ))
            ->getMock();
        $mockTrans
            ->expects($this->once())
            ->method('getResource')
            ->will($this->returnValue($mockTransResource));
        $mockTransResource
            ->expects($this->once())
            ->method('save')
            ->will($this->throwException(new Exception));
        /* config */
        $mockCfg = $this
            ->getMockBuilder('Praxigento_Bonus_Config')
            ->setMethods(array( 'connectionWrite', 'modelTransaction' ))
            ->getMock();
        $mockCfg
            ->expects($this->any())
            ->method('connectionWrite')
            ->will($this->returnValue($mockConn));
        $mockCfg
            ->expects($this->any())
            ->method('modelTransaction')
            ->will($this->returnValue($mockTrans));
        /* setup Config */
        Config::set($mockCfg);
        /**
         * Prepare request and perform call.
         */
        $call = Config::get()->serviceOperations();
        $req  = $call->requestCreateTransaction();
        $req->setCreditAccId($CREDIT_ACC_ID);
        $req->setDebitAccId($DEBIT_ACC_ID);
        $req->setValue($VALUE);
        $req->setOperationId($OPER_ID);
        $req->setDateApplied($DATE_APPLIED);
        $resp = $call->createTransaction($req);
        $this->assertTrue($resp instanceof Praxigento_Bonus_Service_Operations_Response_CreateTransaction);
        $this->assertFalse($resp->isSucceed());
    }

    public function test_updateBalance_accountExists() {
        $ACC_ID    = 321;
        $VAL_SAVED = 546;
        $VAL_INC   = -32;
        /**
         * Create mocks.
         */
        /* existing item  */
        $mockBalance = $this
            ->getMockBuilder('Praxigento_Bonus_Model_Own_Balance')
            ->setMethods(array( 'getData', 'setData', 'getResource' ))
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
        $mockBalanceResource = $this
            ->getMockBuilder('Praxigento_Bonus_Resource_Own_Balance')
            ->setMethods(array( 'save' ))
            ->getMock();
        $mockBalance
            ->expects($this->at(2))
            ->method('getResource')
            ->will($this->returnValue($mockBalanceResource));
        $mockBalanceResource
            ->expects($this->once())
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
        $req->setPeriod(Config::PERIOD_KEY_NOW);
        $resp = $call->updateBalance($req);
        $this->assertTrue($resp instanceof Praxigento_Bonus_Service_Operations_Response_UpdateBalance);
        $this->assertTrue($resp->isSucceed());
        $this->assertTrue($resp->getBalance() instanceof Praxigento_Bonus_Model_Own_Balance);
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
            ->setMethods(array( 'getResource', 'getData', 'setData' ))
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
        $mockBalanceResource = $this
            ->getMockBuilder('Praxigento_Bonus_Resource_Own_Balance')
            ->setMethods(array( 'save' ))
            ->getMock();
        $mockBalance
            ->expects($this->once())
            ->method('getResource')
            ->will($this->returnValue($mockBalanceResource));
        $mockBalanceResource
            ->expects($this->once())
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

    public function test_response_GetOperationsForPvWriteOff() {
        $resp = Mage::getModel('prxgt_bonus_service/operations_response_getOperationsForPvWriteOff');
        $resp->setCollection(new Varien_Data_Collection());
        $this->assertTrue($resp->getCollection() instanceof Varien_Data_Collection);
    }

}