<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Service_Period_Response_RegisterPeriodCalculation
    extends Praxigento_Bonus_Service_Base_Response
{
    /** @var  Praxigento_Bonus_Model_Own_Period */
    private $_period;
    /** @var  Praxigento_Bonus_Model_Own_Log_Calc */
    private $_logCalc;

    /**
     * @return Praxigento_Bonus_Model_Own_Log_Calc
     */
    public function getLogCalc()
    {
        return $this->_logCalc;
    }

    /**
     * @param Praxigento_Bonus_Model_Own_Log_Calc $val
     */
    public function setLogCalc($val)
    {
        $this->_logCalc = $val;
    }

    /**
     * @return Praxigento_Bonus_Model_Own_Period
     */
    public function getPeriod()
    {
        return $this->_period;
    }

    /**
     * @param Praxigento_Bonus_Model_Own_Period $val
     */
    public function setPeriod($val)
    {
        $this->_period = $val;
    }


    public function isSucceed()
    {
        $result = ($this->getErrorCode() == self::ERR_NO_ERROR);
        return $result;
    }
}