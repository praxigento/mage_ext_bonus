<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Service_Period_Request_RegisterPeriodCalculation
    extends Praxigento_Bonus_Service_Base_Request
{
    private $_periodId;
    private $_logCalcId;
    private $_periodValue;
    private $_typeCalcId;
    private $_typePeriodId;

    /**
     * @return mixed
     */
    public function getPeriodValue()
    {
        return $this->_periodValue;
    }

    /**
     * @param mixed $val
     */
    public function setPeriodValue($val)
    {
        $this->_periodValue = $val;
    }

    /**
     * @return mixed
     */
    public function getTypeCalcId()
    {
        return $this->_typeCalcId;
    }

    /**
     * @param mixed $val
     */
    public function setTypeCalcId($val)
    {
        $this->_typeCalcId = $val;
    }

    /**
     * @return mixed
     */
    public function getTypePeriodId()
    {
        return $this->_typePeriodId;
    }

    /**
     * @param mixed $val
     */
    public function setTypePeriodId($val)
    {
        $this->_typePeriodId = $val;
    }

    /**
     * @return mixed
     */
    public function getLogCalcId()
    {
        return $this->_logCalcId;
    }

    /**
     * @param mixed $val
     */
    public function setLogCalcId($val)
    {
        $this->_logCalcId = $val;
    }

    /**
     * @return mixed
     */
    public function getPeriodId()
    {
        return $this->_periodId;
    }

    /**
     * @param mixed $val
     */
    public function setPeriodId($val)
    {
        $this->_periodId = $val;
    }
}