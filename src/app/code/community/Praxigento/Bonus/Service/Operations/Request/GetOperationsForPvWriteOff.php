<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Service_Operations_Request_GetOperationsForPvWriteOff
    extends Praxigento_Bonus_Service_Base_Request
{
    private $logCalcId;
    private $periodCode;
    private $periodValue;

    /**
     * @return mixed
     */
    public function getPeriodValue()
    {
        return $this->periodValue;
    }

    /**
     * @param mixed $val
     */
    public function setPeriodValue($val)
    {
        $this->periodValue = $val;
    }

    /**
     * @return mixed
     */
    public function getPeriodCode()
    {
        return $this->periodCode;
    }

    /**
     * @param mixed $val
     */
    public function setPeriodCode($val)
    {
        $this->periodCode = $val;
    }

    /**
     * @return mixed
     */
    public function getLogCalcId()
    {
        return $this->logCalcId;
    }

    /**
     * @param mixed $val
     */
    public function setLogCalcId($val)
    {
        $this->logCalcId = $val;
    }
}