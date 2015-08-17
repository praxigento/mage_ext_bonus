<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Service_Period_Response_GetPeriodForPvWriteOff
    extends Praxigento_Bonus_Service_Base_Response
{
    /** There is no periods and there is no transactions to process */
    const ERR_NOTHING_TO_DO = 'nothing_to_do';

    /** @var string 20150601 | 201506 | 2015 */
    private $periodValue;
    /** @var bool 'true' - we need register this period on processing */
    private $isNewPeriod;
    /** @var  int ID of the existing period if found. */
    private $existingPeriodId;
    /** @var  int ID of the correspondent calculation log entry */
    private $existingLogCalcId;

    /**
     * @return int
     */
    public function getExistingLogCalcId()
    {
        return $this->existingLogCalcId;
    }

    /**
     * @param int $existingLogCalcId
     */
    public function setExistingLogCalcId($existingLogCalcId)
    {
        $this->existingLogCalcId = $existingLogCalcId;
    }

    /**
     * @return int
     */
    public function getExistingPeriodId()
    {
        return $this->existingPeriodId;
    }

    /**
     * @param int $val
     */
    public function setExistingPeriodId($val)
    {
        $this->existingPeriodId = $val;
    }

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