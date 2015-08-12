<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;
use Praxigento_Bonus_Model_Own_Source_Weekday as Weekday;

/**
 * Period calculation utilities.
 *
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Helper_Period
{
    /** @var Nmmlm_Core_Helper_Data */
    private $_helperCore;
    /** @var Praxigento_Bonus_Helper_Data */
    private $_helper;

    /**
     * * This method is used to inject mocks in unit tests.
     *
     * @param Praxigento_Bonus_Helper_Data $helper
     */
    public function setHelper($helper)
    {
        $this->_helper = $helper;
    }

    /** @var array Common cache for periods bounds: [period][type][from|to] = ... */
    private static $_cachePeriodBounds = array();
    private static $_tzDelta = null;

    function __construct()
    {
        $this->_helperCore = Nmmlm_Core_Config::helper();
        $this->_helper = Config::helper();
        if (is_null(self::$_tzDelta)) {
            /* initiate Timezone delta once */
            self::$_tzDelta = Mage::getSingleton('core/date')->getGmtOffset();
        }
    }

    /**
     * Return current period for given $date and $type of the period (day, week, ...).
     *
     * @param $date
     * @param $type
     * @return null|string 20150601 | 201506 | 2015
     */
    public function calcPeriodCurrent($date, $type)
    {
        $result = null;
        $dt = $this->_helperCore->convertToDateTime($date);
        switch ($type) {
            case Config::PERIOD_DAY:
                $result = date_format($dt, 'Ymd');
                break;
            case Config::PERIOD_WEEK:
                $weekDay = date('w', $dt->getTimestamp());
                if ($weekDay != 0) {
                    /* week period ends on ...  */
                    $end = $this->_helper->cfgPersonalBonusWeekLastDay();
                    $ts = strtotime("next $end", $dt->getTimestamp());
                    $dt = $this->_helperCore->convertToDateTime($ts);
                }
                $result = date_format($dt, 'Ymd');
                break;
            case Config::PERIOD_MONTH:
                $result = date_format($dt, 'Ym');
                break;
            case Config::PERIOD_YEAR:
                $result = date_format($dt, 'Y');
                break;
        }
        return $result;
    }

    /**
     * Calculate period next for the given.
     *
     * @param $period 20150601 | 201506 | 2015
     * @param $type
     * @return null|string 20150601 | 201506 | 2015
     */
    public function calcPeriodNext($period, $type)
    {
        $result = null;
        switch ($type) {
            case Config::PERIOD_DAY:
                $dt = date_create_from_format('Ymd', $period);
                $ts = strtotime('next day', $dt->getTimestamp());
                $dt = $this->_helperCore->convertToDateTime($ts);
                $result = date_format($dt, 'Ymd');
                break;
            case Config::PERIOD_WEEK:
                /* week period ends on ...  */
                $end = $this->_helper->cfgPersonalBonusWeekLastDay();
                $dt = date_create_from_format('Ymd', $period);
                $ts = strtotime("next $end", $dt->getTimestamp());
                $dt = $this->_helperCore->convertToDateTime($ts);
                $result = date_format($dt, 'Ymd');
                break;
            case Config::PERIOD_MONTH:
                $dt = date_create_from_format('Ym', $period);
                $ts = strtotime('next month', $dt->getTimestamp());
                $dt = $this->_helperCore->convertToDateTime($ts);
                $result = date_format($dt, 'Ym');
                break;
            case Config::PERIOD_YEAR:
                $dt = date_create_from_format('Y', $period);
                $ts = strtotime('next year', $dt->getTimestamp());
                $dt = $this->_helperCore->convertToDateTime($ts);
                $result = date_format($dt, 'Y');
                break;
        }
        return $result;
    }

    public function calcPeriodFromTs($period, $type)
    {
        if (
            !isset(self::$_cachePeriodBounds[$period]) &&
            !isset(self::$_cachePeriodBounds[$period][$type])
        ) {
            $this->_calcPeriodBounds($period, $type);
        }
        $result = self::$_cachePeriodBounds[$period][$type]['from'];
        return $result;
    }

    public function calcPeriodToTs($period, $type)
    {
        if (
            !isset(self::$_cachePeriodBounds[$period]) &&
            !isset(self::$_cachePeriodBounds[$period][$type])
        ) {
            $this->_calcPeriodBounds($period, $type);
        }
        $result = self::$_cachePeriodBounds[$period][$type]['to'];
        return $result;
    }

    /**
     * Return "friday" for "saturday", etc.
     * @param $day
     * @return string
     */
    public function getPreviousWeekDay($day)
    {
        $result = null;
        switch (strtolower($day)) {
            case Weekday::SUNDAY:
                $result = Weekday::SATURDAY;
                break;
            case Weekday::MONDAY:
                $result = Weekday::SUNDAY;
                break;
            case Weekday::TUESDAY:
                $result = Weekday::MONDAY;
                break;
            case Weekday::WEDNESDAY:
                $result = Weekday::TUESDAY;
                break;
            case Weekday::THURSDAY:
                $result = Weekday::WEDNESDAY;
                break;
            case Weekday::FRIDAY:
                $result = Weekday::THURSDAY;
                break;
            case Weekday::SATURDAY:
                $result = Weekday::FRIDAY;
                break;
        }
        return $result;
    }

    /**
     * Return "saturday" for "friday", etc.
     * @param $day
     * @return string
     */
    public function getNextWeekDay($day)
    {
        $result = null;
        switch (strtolower($day)) {
            case Weekday::SUNDAY:
                $result = Weekday::MONDAY;
                break;
            case Weekday::MONDAY:
                $result = Weekday::TUESDAY;
                break;
            case Weekday::TUESDAY:
                $result = Weekday::WEDNESDAY;
                break;
            case Weekday::WEDNESDAY:
                $result = Weekday::THURSDAY;
                break;
            case Weekday::THURSDAY:
                $result = Weekday::FRIDAY;
                break;
            case Weekday::FRIDAY:
                $result = Weekday::SATURDAY;
                break;
            case Weekday::SATURDAY:
                $result = Weekday::SUNDAY;
                break;
        }
        return $result;
    }

    /**
     * Calculate period's from/to bounds (month 201508 = "2015-08-01 02:00:00 / 2015-09-01 01:59:59") and cache it.
     *
     * @param $period
     * @param $type
     */
    private function _calcPeriodBounds($period, $type)
    {
        $from = null;
        $to = null;

        switch ($type) {
            case Config::PERIOD_DAY:
                $dt = date_create_from_format('Ymd', $period);
                $ts = strtotime('midnight', $dt->getTimestamp());
                $ts -= self::$_tzDelta;
                $from = date(Config::FROMAT_DATETIME_SQL, $ts);
                $ts = strtotime('tomorrow midnight -1 second', $dt->getTimestamp());
                $ts -= self::$_tzDelta;
                $to = date(Config::FROMAT_DATETIME_SQL, $ts);
                break;
            case Config::PERIOD_WEEK:
                /* week period ends on ...  */
                $end = $this->_helper->cfgPersonalBonusWeekLastDay();
                $prev = $this->getNextWeekDay($end);
                /* this should be the last day of the week */
                $dt = date_create_from_format('Ymd', $period);
                $ts = strtotime("previous $prev midnight", $dt->getTimestamp());
                $ts -= self::$_tzDelta;
                $from = date(Config::FROMAT_DATETIME_SQL, $ts);
                $ts = strtotime('tomorrow midnight -1 second', $dt->getTimestamp());
                $ts -= self::$_tzDelta;
                $to = date(Config::FROMAT_DATETIME_SQL, $ts);
                break;
            case Config::PERIOD_MONTH:
                $dt = date_create_from_format('Ym', $period);
                $ts = strtotime('first day of midnight', $dt->getTimestamp());
                $ts -= self::$_tzDelta;
                $from = date(Config::FROMAT_DATETIME_SQL, $ts);
                $ts = strtotime('first day of next month midnight -1 second', $dt->getTimestamp());
                $ts -= self::$_tzDelta;
                $to = date(Config::FROMAT_DATETIME_SQL, $ts);
                break;
            case Config::PERIOD_YEAR:
                $dt = date_create_from_format('Y', $period);
                $ts = strtotime('first day of January', $dt->getTimestamp());
                $ts -= self::$_tzDelta;
                $from = date(Config::FROMAT_DATETIME_SQL, $ts);
                $ts = strtotime('first day of January next year midnight -1 second', $dt->getTimestamp());
                $ts -= self::$_tzDelta;
                $to = date(Config::FROMAT_DATETIME_SQL, $ts);
                break;
        }
        self::$_cachePeriodBounds[$period][$type]['from'] = $from;
        self::$_cachePeriodBounds[$period][$type]['to'] = $to;
    }
}