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
class Praxigento_Bonus_Test_Helper_Period_UnitTest extends PHPUnit_Framework_TestCase
{

    public function test_calcPeriodCurrent()
    {
        /**
         * Create mocks.
         */
        $helper = $this->getMock('Praxigento_Bonus_Helper_Data');
        $helper->expects($this->any())->method('cfgPersonalBonusWeekLastDay')->will($this->returnValue(Weekday::SUNDAY));
        /** @var  $hlp Praxigento_Bonus_Helper_Period */
        $hlp = Config::helperPeriod();
        $hlp->setHelper($helper);
        $date = '2015-01-05 14:32:32';
        /* tests */
        $this->assertEquals('20150105', $hlp->calcPeriodCurrent($date, Config::PERIOD_DAY));
        $this->assertEquals('20150111', $hlp->calcPeriodCurrent($date, Config::PERIOD_WEEK));
        $this->assertEquals('20150111', $hlp->calcPeriodCurrent('2015-01-11 14:32:32', Config::PERIOD_WEEK));
        $this->assertEquals('201501', $hlp->calcPeriodCurrent($date, Config::PERIOD_MONTH));
        $this->assertEquals('2015', $hlp->calcPeriodCurrent($date, Config::PERIOD_YEAR));
    }

    public function test_calcPeriodNext()
    {
        /**
         * Create mocks.
         */
        $helper = $this->getMock('Praxigento_Bonus_Helper_Data');
        $helper->expects($this->any())->method('cfgPersonalBonusWeekLastDay')->will($this->returnValue(Weekday::SUNDAY));
        /** @var  $hlp Praxigento_Bonus_Helper_Period */
        $hlp = Config::helperPeriod();
        $hlp->setHelper($helper);
        /* tests */
        $this->assertEquals('20150105', $hlp->calcPeriodNext('20150104', Config::PERIOD_DAY));
        $this->assertEquals('20150111', $hlp->calcPeriodNext('20150105', Config::PERIOD_WEEK));
        $this->assertEquals('201501', $hlp->calcPeriodNext('201412', Config::PERIOD_MONTH));
        $this->assertEquals('2015', $hlp->calcPeriodNext('2014', Config::PERIOD_YEAR));
    }


    public function test_calcPeriodFromToTs()
    {
        /**
         * Create mocks.
         */
        $helper = $this->getMock('Praxigento_Bonus_Helper_Data');
        $helper->expects($this->any())->method('cfgPersonalBonusWeekLastDay')->will($this->returnValue(Weekday::SUNDAY));
        /** @var  $hlp Praxigento_Bonus_Helper_Period */
        $hlp = Config::helperPeriod();
        $hlp->setHelper($helper);
        /* tests for zone "America/Los_Angeles" as set up in test/templates.json */
        $this->assertEquals('2015-08-12 07:00:00', $hlp->calcPeriodFromTs('20150812', Config::PERIOD_DAY));
        $this->assertEquals('2015-08-13 06:59:59', $hlp->calcPeriodToTs('20150812', Config::PERIOD_DAY));
        $this->assertEquals('2015-08-10 07:00:00', $hlp->calcPeriodFromTs('20150816', Config::PERIOD_WEEK));
        $this->assertEquals('2015-08-17 06:59:59', $hlp->calcPeriodToTs('20150816', Config::PERIOD_WEEK));
        $this->assertEquals('2015-08-01 07:00:00', $hlp->calcPeriodFromTs('201508', Config::PERIOD_MONTH));
        $this->assertEquals('2015-09-01 06:59:59', $hlp->calcPeriodToTs('201508', Config::PERIOD_MONTH));
        /* switch from and to sequence to cover calcPeriodToTs() branches */
        $this->assertEquals('2016-01-01 06:59:59', $hlp->calcPeriodToTs('2015', Config::PERIOD_YEAR));
        $this->assertEquals('2015-01-01 07:00:00', $hlp->calcPeriodFromTs('2015', Config::PERIOD_YEAR));
    }

    public function test_getPreviousWeekDay()
    {
        /** @var  $hlp Praxigento_Bonus_Helper_Period */
        $hlp = Config::helperPeriod();
        $this->assertEquals(Weekday::SATURDAY, $hlp->getPreviousWeekDay(Weekday::SUNDAY));
        $this->assertEquals(Weekday::SUNDAY, $hlp->getPreviousWeekDay(Weekday::MONDAY));
        $this->assertEquals(Weekday::MONDAY, $hlp->getPreviousWeekDay(Weekday::TUESDAY));
        $this->assertEquals(Weekday::TUESDAY, $hlp->getPreviousWeekDay(Weekday::WEDNESDAY));
        $this->assertEquals(Weekday::WEDNESDAY, $hlp->getPreviousWeekDay(Weekday::THURSDAY));
        $this->assertEquals(Weekday::THURSDAY, $hlp->getPreviousWeekDay(Weekday::FRIDAY));
        $this->assertEquals(Weekday::FRIDAY, $hlp->getPreviousWeekDay(Weekday::SATURDAY));
    }

    public function test_getNextWeekDay()
    {
        /** @var  $hlp Praxigento_Bonus_Helper_Period */
        $hlp = Config::helperPeriod();
        $this->assertEquals(Weekday::SATURDAY, $hlp->getNextWeekDay(Weekday::FRIDAY));
        $this->assertEquals(Weekday::SUNDAY, $hlp->getNextWeekDay(Weekday::SATURDAY));
        $this->assertEquals(Weekday::MONDAY, $hlp->getNextWeekDay(Weekday::SUNDAY));
        $this->assertEquals(Weekday::TUESDAY, $hlp->getNextWeekDay(Weekday::MONDAY));
        $this->assertEquals(Weekday::WEDNESDAY, $hlp->getNextWeekDay(Weekday::TUESDAY));
        $this->assertEquals(Weekday::THURSDAY, $hlp->getNextWeekDay(Weekday::WEDNESDAY));
        $this->assertEquals(Weekday::FRIDAY, $hlp->getNextWeekDay(Weekday::THURSDAY));
    }

    /**
     * Create mock with disabled constructor for class $clazz.
     * @param $clazz
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function mockClass($clazz)
    {
        $mockBuilder = $this->getMockBuilder($clazz);
        $result = $mockBuilder
            ->disableOriginalConstructor()
            ->getMock();
        return $result;
    }
}