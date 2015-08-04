<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */

/**
 * Period calculation utilities.
 *
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Helper_Period
{
    private $_helperCore;

    function __construct()
    {
        $this->_helperCore = Nmmlm_Core_Config::helper();
    }

    /**
     * Return current period for given $date and $type of the period (day, week, ...).
     *
     * @param $date
     * @param $type
     * @return bool|null|string
     */
    public function calcPeriodCurrent($date, $type)
    {
        $result = null;
        $dt = $this->_helperCore->convertToDateTime($date);
        switch ($type) {
            case Praxigento_Bonus_Config::PERIOD_DAY:
                $result = date_format($dt, 'Ymd');
                break;
            case Praxigento_Bonus_Config::PERIOD_WEEK:
                $weekDay = date('w', $dt->getTimestamp());
                if ($weekDay != 0) {
                    $ts = strtotime('next sunday', $dt->getTimestamp());
                    $dt = $this->_helperCore->convertToDateTime($ts);
                }
                $result = date_format($dt, 'Ymd');
                break;
            case Praxigento_Bonus_Config::PERIOD_MONTH:
                $result = date_format($dt, 'Ym');
                break;
            case Praxigento_Bonus_Config::PERIOD_YEAR:
                $result = date_format($dt, 'Y');
                break;
        }
        return $result;
    }

    public function calcPeriodNext($period, $type)
    {
        $result = null;
        switch ($type) {
            case Praxigento_Bonus_Config::PERIOD_DAY:
                $dt = date_create_from_format('Ymd', $period);
                $ts = strtotime('next day', $dt->getTimestamp());
                $dt = $this->_helperCore->convertToDateTime($ts);
                $result = date_format($dt, 'Ymd');
                break;
            case Praxigento_Bonus_Config::PERIOD_WEEK:
                $dt = date_create_from_format('Ymd', $period);
                $ts = strtotime('next sunday', $dt->getTimestamp());
                $dt = $this->_helperCore->convertToDateTime($ts);
                $result = date_format($dt, 'Ymd');
                break;
            case Praxigento_Bonus_Config::PERIOD_MONTH:
                $dt = date_create_from_format('Ym', $period);
                $ts = strtotime('next month', $dt->getTimestamp());
                $dt = $this->_helperCore->convertToDateTime($ts);
                $result = date_format($dt, 'Ym');
                break;
            case Praxigento_Bonus_Config::PERIOD_YEAR:
                $dt = date_create_from_format('Y', $period);
                $ts = strtotime('next year', $dt->getTimestamp());
                $dt = $this->_helperCore->convertToDateTime($ts);
                $result = date_format($dt, 'Y');
                break;
        }
        return $result;
    }
}