<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;

include_once('../phpunit_bootstrap.php');

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Test_Helper_Period_UnitTest extends PHPUnit_Framework_TestCase
{

    public function test_calcPeriodCurrent()
    {
        /** @var  $hlp Praxigento_Bonus_Helper_Period */
        $hlp = Mage::helper(Config::CFG_HELPER . '/period');
        $date = '2015-01-05 14:32:32';
        /* tests */
        $this->assertEquals('20150105', $hlp->calcPeriodCurrent($date, Praxigento_Bonus_Config::PERIOD_DAY));
        $this->assertEquals('20150111', $hlp->calcPeriodCurrent($date, Praxigento_Bonus_Config::PERIOD_WEEK));
        $this->assertEquals('20150111', $hlp->calcPeriodCurrent('2015-01-11 14:32:32', Praxigento_Bonus_Config::PERIOD_WEEK));
        $this->assertEquals('201501', $hlp->calcPeriodCurrent($date, Praxigento_Bonus_Config::PERIOD_MONTH));
        $this->assertEquals('2015', $hlp->calcPeriodCurrent($date, Praxigento_Bonus_Config::PERIOD_YEAR));
    }

    public function test_calcPeriodNext()
    {
        /** @var  $hlp Praxigento_Bonus_Helper_Period */
        $hlp = Mage::helper(Config::CFG_HELPER . '/period');
        $date = '2015-01-05 14:32:32';
        /* tests */
        $this->assertEquals('20150105', $hlp->calcPeriodNext('20150104', Praxigento_Bonus_Config::PERIOD_DAY));
        $this->assertEquals('20150111', $hlp->calcPeriodNext('20150105', Praxigento_Bonus_Config::PERIOD_WEEK));
        $this->assertEquals('201501', $hlp->calcPeriodNext('201412', Praxigento_Bonus_Config::PERIOD_MONTH));
        $this->assertEquals('2015', $hlp->calcPeriodNext('2014', Praxigento_Bonus_Config::PERIOD_YEAR));
    }
}