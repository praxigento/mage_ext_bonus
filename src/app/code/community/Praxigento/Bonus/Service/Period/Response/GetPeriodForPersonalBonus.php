<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Service_Period_Response_GetPeriodForPersonalBonus
    extends Praxigento_Bonus_Service_Base_Response
{
    public $periodValue;

    /**
     * @return string 20150601 | 201506 | 2015
     */
    public function getPeriodValue()
    {
        return $this->periodValue;
    }

    /**
     * @param string $val 20150601 | 201506 | 2015
     */
    public function setPeriodValue($val)
    {
        $this->periodValue = $val;
    }

    public function isSucceed()
    {
        $result = ($this->getErrorCode() == self::ERR_NO_ERROR);
        return $result;
    }
}