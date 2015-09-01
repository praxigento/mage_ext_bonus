<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */

/**
 * Base class for "GetPeriodForPersonalBonus" & "GetPeriodForPvWriteOff" responses.
 * User: Alex Gusev <alex@flancer64.com>
 */
abstract class Praxigento_Bonus_Service_Period_Response_GetPeriodForPersonalBonusBase
    extends Praxigento_Bonus_Service_Base_Response {
    /** There is no periods and there is no transactions to process */
    const ERR_NOTHING_TO_DO = 'nothing_to_do';
    /** @var  int */
    private $calculationTypeId;
    /** @var  int ID of the correspondent calculation log entry */
    private $existingLogCalcId;
    /** @var  int ID of the existing period if found. */
    private $existingPeriodId;
    /** @var  string DAY | WEEK | ... */
    private $periodTypeCode;
    /** @var  int */
    private $periodTypeId;
    /** @var string 20150601 | 201506 | 2015 */
    private $periodValue;

    /**
     * @return int
     */
    public function getCalculationTypeId() {
        return $this->calculationTypeId;
    }

    /**
     * @param int $val
     */
    public function setCalculationTypeId($val) {
        $this->calculationTypeId = $val;
    }

    /**
     * @return string
     */
    public function getPeriodTypeCode() {
        return $this->periodTypeCode;
    }

    /**
     * @param string $val
     */
    public function setPeriodTypeCode($val) {
        $this->periodTypeCode = $val;
    }

    /**
     * @return int
     */
    public function getPeriodTypeId() {
        return $this->periodTypeId;
    }

    /**
     * @param int $val
     */
    public function setPeriodTypeId($val) {
        $this->periodTypeId = $val;
    }

    /**
     * @return int
     */
    public function getExistingLogCalcId() {
        return $this->existingLogCalcId;
    }

    /**
     * @param int $val
     */
    public function setExistingLogCalcId($val) {
        $this->existingLogCalcId = $val;
    }

    /**
     * @return int
     */
    public function getExistingPeriodId() {
        return $this->existingPeriodId;
    }

    /**
     * @param int $val
     */
    public function setExistingPeriodId($val) {
        $this->existingPeriodId = $val;
    }

    /**
     * @return string 20150601 | 201506 | 2015
     */
    public function getPeriodValue() {
        return $this->periodValue;
    }

    /**
     * @param string $val 20150601 | 201506 | 2015
     */
    public function setPeriodValue($val) {
        $this->periodValue = $val;
    }

    public function isSucceed() {
        $result = ($this->getErrorCode() == self::ERR_NO_ERROR);
        return $result;
    }
}