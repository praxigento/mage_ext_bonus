<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;
use Praxigento_Bonus_Model_Own_Source_Weekday as Weekday;

include_once('../phpunit_bootstrap.php');

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Test_Helper_Period_UnitTest extends PHPUnit_Framework_TestCase {

    public function setUp() {
        /* reset module's Config instance */
        Config::set(null);
    }

    public function test_calcPeriodCurrent() {
        /**
         * Create mocks.
         */
        $helper = $this->getMock('Praxigento_Bonus_Helper_Data');
        $helper->expects($this->any())->method('cfgPersonalBonusWeekLastDay')->will($this->returnValue(Weekday::SUNDAY));
        /** @var  $hlp Praxigento_Bonus_Helper_Period */
        $hlp = Config::get()->helperPeriod();
        $hlp->setHelper($helper);
        $date = '2015-01-05 14:32:32';
        /* tests */
        $this->assertEquals('20150105', $hlp->calcPeriodCurrent($date, Config::PERIOD_DAY));
        $this->assertEquals('20150111', $hlp->calcPeriodCurrent($date, Config::PERIOD_WEEK));
        $this->assertEquals('20150111', $hlp->calcPeriodCurrent('2015-01-11 14:32:32', Config::PERIOD_WEEK));
        $this->assertEquals('201501', $hlp->calcPeriodCurrent($date, Config::PERIOD_MONTH));
        $this->assertEquals('2015', $hlp->calcPeriodCurrent($date, Config::PERIOD_YEAR));
    }

    public function test_calcPeriodNext() {
        /**
         * Create mocks.
         */
        $helper = $this->getMock('Praxigento_Bonus_Helper_Data');
        $helper->expects($this->any())->method('cfgPersonalBonusWeekLastDay')->will($this->returnValue(Weekday::SUNDAY));
        /** @var  $hlp Praxigento_Bonus_Helper_Period */
        $hlp = Config::get()->helperPeriod();
        $hlp->setHelper($helper);
        /* tests */
        $this->assertEquals('20150105', $hlp->calcPeriodNext('20150104', Config::PERIOD_DAY));
        $this->assertEquals('20150111', $hlp->calcPeriodNext('20150105', Config::PERIOD_WEEK));
        $this->assertEquals('201501', $hlp->calcPeriodNext('201412', Config::PERIOD_MONTH));
        $this->assertEquals('2015', $hlp->calcPeriodNext('2014', Config::PERIOD_YEAR));
    }

    public function test_calcPeriodPrev() {
        /**
         * Create mocks.
         */
        $helper = $this->getMock('Praxigento_Bonus_Helper_Data');
        $helper->expects($this->any())->method('cfgPersonalBonusWeekLastDay')->will($this->returnValue(Weekday::SUNDAY));
        /** @var  $hlp Praxigento_Bonus_Helper_Period */
        $hlp = Config::get()->helperPeriod();
        $hlp->setHelper($helper);
        /* tests */
        $this->assertEquals('20150103', $hlp->calcPeriodPrev('20150104', Config::PERIOD_DAY));
        $this->assertEquals('20150104', $hlp->calcPeriodPrev('20150105', Config::PERIOD_WEEK));
        $this->assertEquals('20141228', $hlp->calcPeriodPrev('20150104', Config::PERIOD_WEEK));
        $this->assertEquals('201312', $hlp->calcPeriodPrev('201401', Config::PERIOD_MONTH));
        $this->assertEquals('2013', $hlp->calcPeriodPrev('2014', Config::PERIOD_YEAR));
    }

    public function test_calcPeriodTs() {
        /**
         * Create mocks.
         */
        $helper = $this->getMock('Praxigento_Bonus_Helper_Data');
        $helper->expects($this->any())->method('cfgPersonalBonusWeekLastDay')->will($this->returnValue(Weekday::SUNDAY));
        /** @var  $hlp Praxigento_Bonus_Helper_Period */
        $hlp = Config::get()->helperPeriod();
        $hlp->setHelper($helper);
        /* tests for zone "America/Los_Angeles" as set up in test/templates.json */
        $this->assertEquals('2015-08-12 07:00:00', $hlp->calcPeriodTsFrom('20150812', Config::PERIOD_DAY));
        $this->assertEquals('2015-08-13 06:59:59', $hlp->calcPeriodTsTo('20150812', Config::PERIOD_DAY));
        $this->assertEquals('2015-08-10 07:00:00', $hlp->calcPeriodTsFrom('20150816', Config::PERIOD_WEEK));
        $this->assertEquals('2015-08-17 06:59:59', $hlp->calcPeriodTsTo('20150816', Config::PERIOD_WEEK));
        $this->assertEquals('2015-08-01 07:00:00', $hlp->calcPeriodTsFrom('201508', Config::PERIOD_MONTH));
        $this->assertEquals('2015-09-01 06:59:59', $hlp->calcPeriodTsTo('201508', Config::PERIOD_MONTH));
        /* switch from and to sequence to cover calcPeriodTsTo() branches */
        $this->assertEquals('2016-01-01 06:59:59', $hlp->calcPeriodTsTo('2015', Config::PERIOD_YEAR));
        $this->assertEquals('2015-01-01 07:00:00', $hlp->calcPeriodTsFrom('2015', Config::PERIOD_YEAR));
        /* tests nextFrom & prevTo */
        $this->assertEquals('2014-12-29 07:00:00', $hlp->calcPeriodTsNextFrom('20141228', Config::PERIOD_WEEK));
        $this->assertEquals('2015-01-05 06:59:59', $hlp->calcPeriodTsPrevTo('20150105', Config::PERIOD_WEEK));
        /* test period 'NOW'*/
        $val = $hlp->calcPeriodTsPrevTo('NOW', Config::PERIOD_WEEK);
        $this->assertTrue(is_string($val));
        $this->assertTrue(strlen($val) == 19);
    }

    public function test_getPreviousWeekDay() {
        /** @var  $hlp Praxigento_Bonus_Helper_Period */
        $hlp = Config::get()->helperPeriod();
        $this->assertEquals(Weekday::SATURDAY, $hlp->getPreviousWeekDay(Weekday::SUNDAY));
        $this->assertEquals(Weekday::SUNDAY, $hlp->getPreviousWeekDay(Weekday::MONDAY));
        $this->assertEquals(Weekday::MONDAY, $hlp->getPreviousWeekDay(Weekday::TUESDAY));
        $this->assertEquals(Weekday::TUESDAY, $hlp->getPreviousWeekDay(Weekday::WEDNESDAY));
        $this->assertEquals(Weekday::WEDNESDAY, $hlp->getPreviousWeekDay(Weekday::THURSDAY));
        $this->assertEquals(Weekday::THURSDAY, $hlp->getPreviousWeekDay(Weekday::FRIDAY));
        $this->assertEquals(Weekday::FRIDAY, $hlp->getPreviousWeekDay(Weekday::SATURDAY));
    }

    public function test_getNextWeekDay() {
        /** @var  $hlp Praxigento_Bonus_Helper_Period */
        $hlp = Config::get()->helperPeriod();
        $this->assertEquals(Weekday::SATURDAY, $hlp->getNextWeekDay(Weekday::FRIDAY));
        $this->assertEquals(Weekday::SUNDAY, $hlp->getNextWeekDay(Weekday::SATURDAY));
        $this->assertEquals(Weekday::MONDAY, $hlp->getNextWeekDay(Weekday::SUNDAY));
        $this->assertEquals(Weekday::TUESDAY, $hlp->getNextWeekDay(Weekday::MONDAY));
        $this->assertEquals(Weekday::WEDNESDAY, $hlp->getNextWeekDay(Weekday::TUESDAY));
        $this->assertEquals(Weekday::THURSDAY, $hlp->getNextWeekDay(Weekday::WEDNESDAY));
        $this->assertEquals(Weekday::FRIDAY, $hlp->getNextWeekDay(Weekday::THURSDAY));
    }

    public function test_isPeriodYear() {
        /** @var  $hlp Praxigento_Bonus_Helper_Period */
        $hlp = Config::get()->helperPeriod();
        $this->assertTrue($hlp->isPeriodYear('2015'));
        $this->assertFalse($hlp->isPeriodYear('201506'));
        $this->assertFalse($hlp->isPeriodYear('20150601'));
    }

    public function test_isPeriodMonth() {
        /** @var  $hlp Praxigento_Bonus_Helper_Period */
        $hlp = Config::get()->helperPeriod();
        $this->assertFalse($hlp->isPeriodMonth('2015'));
        $this->assertTrue($hlp->isPeriodMonth('201506'));
        $this->assertFalse($hlp->isPeriodMonth('20150601'));
    }

    public function test_isPeriodDay() {
        /** @var  $hlp Praxigento_Bonus_Helper_Period */
        $hlp = Config::get()->helperPeriod();
        $this->assertFalse($hlp->isPeriodDay('2015'));
        $this->assertFalse($hlp->isPeriodDay('201506'));
        $this->assertTrue($hlp->isPeriodDay('20150601'));
    }

    public function test_calcPeriodSmallest() {
        /** @var  $hlp Praxigento_Bonus_Helper_Period */
        $hlp = Config::get()->helperPeriod();
        $this->assertEquals('20151231', $hlp->calcPeriodSmallest('2015'));
        $this->assertEquals('20150630', $hlp->calcPeriodSmallest('201506'));
        $this->assertEquals('20150908', $hlp->calcPeriodSmallest('20150908'));
    }
}