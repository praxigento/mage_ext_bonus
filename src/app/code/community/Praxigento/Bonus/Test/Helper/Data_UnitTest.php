<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Nmmlm_Core_Config as ConfigCore;
use Praxigento_Bonus_Config as Config;

include_once(dirname(__FILE__) . '/../phpunit_bootstrap.php');

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Test_Helper_Data_UnitTest extends PHPUnit_Framework_TestCase {
    /**
     * Reset Config before each test.
     */
    public function setUp() {
        Config::set(null);
    }

    public function test_cfg() {
        /** @var  $hlp Praxigento_Bonus_Helper_Data */
        $hlp = Config::get()->helper();
        $this->assertTrue(is_string($hlp->cfgGeneralAccountantMlmId()));
        $this->assertTrue(is_numeric($hlp->cfgGeneralDownlineDepth()));
        $this->assertTrue(is_bool($hlp->cfgPersonalBonusEnabled()));
        $this->assertTrue(is_string($hlp->cfgPersonalBonusPeriod()));
        $this->assertTrue(is_string($hlp->cfgPersonalBonusWeekLastDay()));
        $this->assertTrue(is_int($hlp->cfgPersonalBonusPayoutDelay()));
        $this->assertTrue(is_bool($hlp->cfgRetailBonusEnabled()));
        $this->assertTrue(is_numeric($hlp->cfgRetailBonusFeeFixed()));
        $this->assertTrue(is_numeric($hlp->cfgRetailBonusFeeMax()));
        $this->assertTrue(is_numeric($hlp->cfgRetailBonusFeeMin()));
        $this->assertTrue(is_numeric($hlp->cfgRetailBonusFeePercent()));
    }

    public function test_formatAmount() {
        /** @var  $hlp Praxigento_Bonus_Helper_Data */
        $hlp = Config::get()->helper();
        $this->assertEquals('1$234@57', $hlp->formatAmount(1234.5678, '@', '$'));
    }

    public function test_getUplineForCustomer() {
        $CUST_MLMID = 'mlmId';
        $CUST_ID    = 21;
        /**
         * Mock config class and core helper.
         */
        /* Found customer */
        $mockCustomer = Mage::getModel('customer/customer');
        $mockCustomer->setId($CUST_ID);
        /* Core Helper */
        $mockBuilder = $this->getMockBuilder('Nmmlm_Core_Helper_Data');
        $mockBuilder->setMethods(array( 'findCustomerByMlmId' ));
        $mockHelperCore = $mockBuilder->getMock();
        $mockHelperCore
            ->expects($this->once())
            ->method('findCustomerByMlmId')
            ->will($this->equalTo($CUST_MLMID))
            ->will($this->returnValue($mockCustomer));
        /* Config */
        $mockBuilder = $this
            ->getMockBuilder('Praxigento_Bonus_Config')
            ->setMethods(array( 'helperCore' ));
        $mockCfg     = $mockBuilder->getMock();
        $mockCfg
            ->expects($this->any())
            ->method('helperCore')
            ->will($this->returnValue($mockHelperCore));
        /* setup Config */
        Config::set($mockCfg);
        /** @var  $hlp Praxigento_Bonus_Helper_Data */
        $hlp      = Config::get()->helper();
        $customer = Mage::getModel('customer/customer');
        $customer->setData(ConfigCore::ATTR_CUST_MLM_UPLINE, 'UplineMlmId');
        $upliner = $hlp->getUplineForCustomer($customer);
        $this->assertTrue($upliner instanceof Nmmlm_Core_Model_Customer_Customer);
        $this->assertEquals($CUST_ID, $upliner->getId());
    }

    public function test_getDateGmtNow() {
        $FORMAT = 'Ymd-His';
        $TIME   = '20150831-121314';
        /**
         * Mock config class and core helper.
         */
        /* Core Helper */
        $mockBuilder = $this->getMockBuilder('Nmmlm_Core_Helper_Data');
        $mockBuilder->setMethods(array( 'dateGmtNow' ));
        $mockHelperCore = $mockBuilder->getMock();
        $mockHelperCore
            ->expects($this->once())
            ->method('dateGmtNow')
            ->with($this->equalTo($FORMAT))
            ->will($this->returnValue($TIME));
        /* Config */
        $mockBuilder = $this
            ->getMockBuilder('Praxigento_Bonus_Config')
            ->setMethods(array( 'helperCore' ));
        $mockCfg     = $mockBuilder->getMock();
        $mockCfg
            ->expects($this->any())
            ->method('helperCore')
            ->will($this->returnValue($mockHelperCore));
        /* setup Config */
        Config::set($mockCfg);
        /** @var  $hlp Praxigento_Bonus_Helper_Data */
        $hlp       = Config::get()->helper();
        $formatted = $hlp->getDateGmtNow($FORMAT);
        $this->assertEquals($TIME, $formatted);
    }


    public function test_getUplineFromSession() {
        $PROC_CLASS   = 'nmmlm_core_model/own_referral_customer_processor';
        $mockBuilder  = $this->getMockBuilder('Nmmlm_Core_Model_Customer_Customer');
        $mockCustomer = $mockBuilder->getMock();
        /* Processor */
        $mockBuilder = $this
            ->getMockBuilder('Nmmlm_Core_Model_Own_Referral_Customer_Processor')
            ->setMethods(array( 'sessionGetUpline' ));
        $mockProc    = $mockBuilder->getMock();
        $mockProc
            ->expects($this->once())
            ->method('sessionGetUpline')
            ->will($this->returnValue($mockCustomer));
        /* Config */
        $mockBuilder = $this
            ->getMockBuilder('Praxigento_Bonus_Config')
            ->setMethods(array( 'singleton' ));
        $mockCfg     = $mockBuilder->getMock();
        $mockCfg
            ->expects($this->once())
            ->method('singleton')
            ->with($this->equalTo($PROC_CLASS))
            ->will($this->returnValue($mockProc));
        /* setup Config */
        Config::set($mockCfg);
        /** @var  $hlp Praxigento_Bonus_Helper_Data */
        $hlp  = Config::get()->helper();
        $cust = $hlp->getUplineFromSession();
        $this->assertTrue($cust instanceof Nmmlm_Core_Model_Customer_Customer);
    }
}