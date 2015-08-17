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
    private $calcTypeId;
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
     * @param mixed $periodValue
     */
    public function setPeriodValue($periodValue)
    {
        $this->periodValue = $periodValue;
    }

    /**
     * @return mixed
     */
    public function getPeriodCode()
    {
        return $this->periodCode;
    }

    /**
     * @param mixed $periodCode
     */
    public function setPeriodCode($periodCode)
    {
        $this->periodCode = $periodCode;
    }

    /**
     * @return mixed
     */
    public function getCalcTypeId()
    {
        return $this->calcTypeId;
    }

    /**
     * @param mixed $calcTypeId
     */
    public function setCalcTypeId($calcTypeId)
    {
        $this->calcTypeId = $calcTypeId;
    }
}