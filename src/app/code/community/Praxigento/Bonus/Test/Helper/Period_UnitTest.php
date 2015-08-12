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
        $hlp = Config::helperPeriod();
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
        /** @var  $hlp Praxigento_Bonus_Helper_Period */
        $hlp = Config::helperPeriod();
        /* tests */
        $this->assertEquals('20150105', $hlp->calcPeriodNext('20150104', Config::PERIOD_DAY));
        $this->assertEquals('20150111', $hlp->calcPeriodNext('20150105', Config::PERIOD_WEEK));
        $this->assertEquals('201501', $hlp->calcPeriodNext('201412', Config::PERIOD_MONTH));
        $this->assertEquals('2015', $hlp->calcPeriodNext('2014', Config::PERIOD_YEAR));
    }


    public function test_calcPeriodFromToTs()
    {
        /** @var  $hlp Praxigento_Bonus_Helper_Period */
        $hlp = Config::helperPeriod();
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
}