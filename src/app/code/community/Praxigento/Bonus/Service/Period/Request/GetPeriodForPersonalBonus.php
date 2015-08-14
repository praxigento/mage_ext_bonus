<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Service_Period_Request_GetPeriodForPersonalBonus
    extends Praxigento_Bonus_Service_Base_Request
{
    /* TODO: should we create base class for PeriodForPersonalBonus & PeriodForPvWriteOff */
    private $periodCode;
    private $periodTypeId;
    private $calcTypeId;
    private $operationTypeIds;

    /**
     * @return mixed
     */
    public function getCalcTypeId()
    {
        return $this->bonusTypeId;
    }

    /**
     * @param mixed $val
     */
    public function setCalcTypeId($val)
    {
        $this->bonusTypeId = $val;
    }

    /**
     * @return mixed
     */
    public function getOperationTypeIds()
    {
        return $this->operationTypeIds;
    }

    /**
     * @param mixed $val
     */
    public function setOperationTypeIds($val)
    {
        $this->operationTypeIds = $val;
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
    public function getPeriodTypeId()
    {
        return $this->periodTypeId;
    }

    /**
     * @param mixed $val
     */
    public function setPeriodTypeId($val)
    {
        $this->periodTypeId = $val;
    }
}