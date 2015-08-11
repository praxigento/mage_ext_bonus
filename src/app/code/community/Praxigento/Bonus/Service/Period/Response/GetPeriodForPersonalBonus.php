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
    /** There is no periods and there is no transactions to process */
    const ERR_NOTHING_TO_DO = 'nothing_to_do';

    /** @var string 20150601 | 201506 | 2015 */
    public $periodValue;
    /** @var bool 'true' - we need register this period on processing */
    public $isNewPeriod;

    /**
     * @return boolean 'true' - we need register this period on processing
     */
    public function isNewPeriod()
    {
        return $this->isNewPeriod;
    }

    /**
     * @param boolean $val 'true' - we need register this period on processing
     */
    public function setIsNewPeriod($val)
    {
        $this->isNewPeriod = $val;
    }

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