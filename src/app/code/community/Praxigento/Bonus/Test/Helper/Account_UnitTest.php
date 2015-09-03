<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;
use Praxigento_Bonus_Model_Own_Account as Account;
use Praxigento_Bonus_Model_Own_Source_Weekday as Weekday;
use Praxigento_Bonus_Model_Own_Type_Asset as TypeAsset;
use Praxigento_Bonus_Model_Own_Type_Base as TypeBase;
use Praxigento_Bonus_Model_Own_Type_Calc as TypeCalc;

include_once('../phpunit_bootstrap.php');

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Test_Helper_Account_UnitTest extends PHPUnit_Framework_TestCase {
    const ACCOUNTANT_MAGE_ID = 14;
    const ACCOUNTANT_MLM_ID = '100000014';
    const ACC_PV_ASSET_ID = 100;
    const ACC_PV_ID = 200;
    const ACC_RETAIL_ASSET_ID = 300;
    const ACC_RETAIL_ID = 400;

    public function setUp() {
        Config::set(null);
    }

    public function test_constructor() {
        $hlp = Config::get()->helperAccount();
        $this->assertNotNull($hlp);
        $this->assertTrue($hlp instanceof Praxigento_Bonus_Helper_Account);
    }

    public function test_getAccountantAccByAssetCode_existingAccount() {
        /* mock environment */
        $this->_mockConfig_getAccountantAccByAssetCode_existingAccount();
        /* test the method */
        $hlp     = Config::get()->helperAccount();
        $account = $hlp->getAccountantAccByAssetCode(Config::ASSET_PV);
        $this->assertEquals(self::ACC_PV_ASSET_ID, $account->getData(Account::ATTR_ASSET_ID));
        $this->assertEquals(self::ACCOUNTANT_MAGE_ID, $account->getData(Account::ATTR_CUSTOMER_ID));
        $this->assertEquals(self::ACC_PV_ID, $account->getData(Account::ATTR_ID));
    }

    private function _mockConfig_getAccountantAccByAssetCode_existingAccount() {
        /* One Account Item (joined) */
        $item = new Varien_Object();
        $item->setData(Account::ATTR_ID, self::ACC_PV_ID);
        $item->setData(Account::ATTR_ASSET_ID, self::ACC_PV_ASSET_ID);
        $item->setData(Account::ATTR_CUSTOMER_ID, self::ACCOUNTANT_MAGE_ID);
        $item->setData(TypeAsset::ATTR_CODE, Config::ASSET_PV);
        /* Accounts collection */
        $mockBuilder = $this->getMockBuilder('Praxigento_Bonus_Resource_Own_Account_Collection');
        $mockBuilder->setMethods(array( 'getIterator' ));
        $mockAccounts = $mockBuilder->getMock();
        $mockAccounts
            ->expects($this->any())
            ->method('getIterator')
            ->will($this->returnValue(new ArrayIterator(array( $item ))));
        /* Accountant customer model */
        $accountantCust = new Varien_Object();
        $accountantCust->setId(self::ACCOUNTANT_MAGE_ID);
        /* helper */
        $mockBuilder = $this->getMockBuilder('Praxigento_Bonus_Helper_Data');
        $mockBuilder->setMethods(array( 'cfgGeneralAccountantMlmId' ));
        $mockHelper = $mockBuilder->getMock();
        $mockHelper
            ->expects($this->any())
            ->method('cfgGeneralAccountantMlmId')
            ->will($this->returnValue(self::ACCOUNTANT_MLM_ID));
        /* core helper */
        $mockBuilder = $this->getMockBuilder('Nmmlm_Core_Helper_Data');
        $mockBuilder->setMethods(array( 'findCustomerByMlmId' ));
        $mockHelperCore = $mockBuilder->getMock();
        $mockHelperCore
            ->expects($this->any())
            ->method('findCustomerByMlmId')
            ->will($this->returnValue($accountantCust));
        /* config */
        $mockBuilder = $this->getMockBuilder('Praxigento_Bonus_Config');
        $mockBuilder->setMethods(array(
            'helper',
            'helperCore',
            'collectionAccount'
        ));
        $mockCfg = $mockBuilder->getMock();
        $mockCfg
            ->expects($this->any())
            ->method('helper')
            ->will($this->returnValue($mockHelper));
        $mockCfg
            ->expects($this->any())
            ->method('helperCore')
            ->will($this->returnValue($mockHelperCore));
        $mockCfg
            ->expects($this->any())
            ->method('collectionAccount')
            ->will($this->returnValue($mockAccounts));
        /* setup Config */
        Config::set($mockCfg);
    }

    public function test_getAccountantAccIdByAssetCode() {
        /* mock environment */
        $this->_mockConfig_getAccountantAccByAssetCode_existingAccount();
        /* test the method */
        $hlp     = Config::get()->helperAccount();
        $accountId = $hlp->getAccountantAccIdByAssetCode(Config::ASSET_PV);
        $this->assertEquals(self::ACC_PV_ID, $accountId);
    }

    public function test_getAccountantAccByAssetCode_newAccount() {
        /* mock environment */
        $this->_mockConfig_getAccountantAccByAssetCode_newAccount();
        /* test the method */
        $hlp     = Config::get()->helperAccount();
        $account = $hlp->getAccountantAccByAssetCode(Config::ASSET_RETAIL);
        $this->assertEquals(self::ACC_RETAIL_ASSET_ID, $account->getData(Account::ATTR_ASSET_ID));
        $this->assertEquals(self::ACCOUNTANT_MAGE_ID, $account->getData(Account::ATTR_CUSTOMER_ID));
        $this->assertEquals(self::ACC_RETAIL_ID, $account->getData(Account::ATTR_ID));
    }

    private function _mockConfig_getAccountantAccByAssetCode_newAccount() {
        /* Account model */
        $mockBuilder = $this->getMockBuilder('Praxigento_Bonus_Model_Own_Account');
        $mockBuilder->setMethods(array( 'getData', 'save' ));
        $mockModelAccount = $mockBuilder->getMock();
        $mockModelAccount
            ->expects($this->once())
            ->method('save');
        /* I don't know why numbering starts from 1 in this case */
        $mockModelAccount
            ->expects($this->at(1))
            ->method('getData')
            ->with($this->equalTo(Account::ATTR_ASSET_ID))
            ->will($this->returnValue(self::ACC_RETAIL_ASSET_ID));
        $mockModelAccount
            ->expects($this->at(2))
            ->method('getData')
            ->with($this->equalTo(Account::ATTR_CUSTOMER_ID))
            ->will($this->returnValue(self::ACCOUNTANT_MAGE_ID));
        $mockModelAccount
            ->expects($this->at(3))
            ->method('getData')
            ->with($this->equalTo(Account::ATTR_ID))
            ->will($this->returnValue(self::ACC_RETAIL_ID));
        /* One Account Item (joined) */
        $item = new Varien_Object();
        $item->setData(Account::ATTR_ID, self::ACC_PV_ID);
        $item->setData(Account::ATTR_ASSET_ID, self::ACC_PV_ASSET_ID);
        $item->setData(Account::ATTR_CUSTOMER_ID, self::ACCOUNTANT_MAGE_ID);
        $item->setData(TypeAsset::ATTR_CODE, Config::ASSET_PV);
        /* Accounts collection */
        $mockBuilder = $this->getMockBuilder('Praxigento_Bonus_Resource_Own_Account_Collection');
        $mockBuilder->setMethods(array( 'getIterator' ));
        $mockAccounts = $mockBuilder->getMock();
        $mockAccounts
            ->expects($this->any())
            ->method('getIterator')
            ->will($this->returnValue(new ArrayIterator(array( $item ))));
        /* Accountant customer model */
        $accountantCust = new Varien_Object();
        $accountantCust->setId(self::ACCOUNTANT_MAGE_ID);
        /* helper */
        $mockBuilder = $this->getMockBuilder('Praxigento_Bonus_Helper_Data');
        $mockBuilder->setMethods(array( 'cfgGeneralAccountantMlmId' ));
        $mockHelper = $mockBuilder->getMock();
        $mockHelper
            ->expects($this->any())
            ->method('cfgGeneralAccountantMlmId')
            ->will($this->returnValue(self::ACCOUNTANT_MLM_ID));
        /* helper type */
        $mockBuilder = $this->getMockBuilder('Praxigento_Bonus_Helper_Data_Type');
        $mockBuilder->setMethods(array( 'getAssetId' ));
        $mockHelperType = $mockBuilder->getMock();
        $mockHelperType
            ->expects($this->any())
            ->method('getAssetId')
            ->will($this->returnValue(self::ACC_RETAIL_ASSET_ID));
        /* core helper */
        $mockBuilder = $this->getMockBuilder('Nmmlm_Core_Helper_Data');
        $mockBuilder->setMethods(array( 'findCustomerByMlmId' ));
        $mockHelperCore = $mockBuilder->getMock();
        $mockHelperCore
            ->expects($this->any())
            ->method('findCustomerByMlmId')
            ->will($this->returnValue($accountantCust));
        /* config */
        $mockBuilder = $this->getMockBuilder('Praxigento_Bonus_Config');
        $mockBuilder->setMethods(array(
            'helper',
            'helperType',
            'helperCore',
            'modelAccount',
            'collectionAccount'
        ));
        $mockCfg = $mockBuilder->getMock();
        $mockCfg
            ->expects($this->any())
            ->method('helper')
            ->will($this->returnValue($mockHelper));
        $mockCfg
            ->expects($this->any())
            ->method('helperType')
            ->will($this->returnValue($mockHelperType));
        $mockCfg
            ->expects($this->any())
            ->method('helperCore')
            ->will($this->returnValue($mockHelperCore));
        $mockCfg
            ->expects($this->any())
            ->method('modelAccount')
            ->will($this->returnValue($mockModelAccount));
        $mockCfg
            ->expects($this->any())
            ->method('collectionAccount')
            ->will($this->returnValue($mockAccounts));
        /* setup Config */
        Config::set($mockCfg);
    }

    public function test_getAccountantMageId() {
        /* mock environment */
        $this->_mockConfig_getAccountantMageId();
        /* test the method */
        $hlp    = Config::get()->helperAccount();
        $mageId = $hlp->getAccountantMageId();
        $this->assertEquals(self::ACCOUNTANT_MAGE_ID, $mageId);
    }

    private function _mockConfig_getAccountantMageId() {
        /* Accountant customer model */
        $accountantCust = new Varien_Object();
        $accountantCust->setId(self::ACCOUNTANT_MAGE_ID);
        /* helper */
        $mockBuilder = $this->getMockBuilder('Praxigento_Bonus_Helper_Data');
        $mockBuilder->setMethods(array( 'cfgGeneralAccountantMlmId' ));
        $mockHelper = $mockBuilder->getMock();
        $mockHelper
            ->expects($this->any())
            ->method('cfgGeneralAccountantMlmId')
            ->will($this->returnValue(self::ACCOUNTANT_MLM_ID));
        /* core helper */
        $mockBuilder = $this->getMockBuilder('Nmmlm_Core_Helper_Data');
        $mockBuilder->setMethods(array( 'findCustomerByMlmId' ));
        $mockHelperCore = $mockBuilder->getMock();
        $mockHelperCore
            ->expects($this->any())
            ->method('findCustomerByMlmId')
            ->will($this->returnValue($accountantCust));
        /* config */
        $mockBuilder = $this->getMockBuilder('Praxigento_Bonus_Config');
        $mockBuilder->setMethods(array( 'helper', 'helperCore' ));
        $mockCfg = $mockBuilder->getMock();
        $mockCfg
            ->expects($this->any())
            ->method('helper')
            ->will($this->returnValue($mockHelper));
        $mockCfg
            ->expects($this->any())
            ->method('helperCore')
            ->will($this->returnValue($mockHelperCore));
        /* setup Config */
        Config::set($mockCfg);
    }
}