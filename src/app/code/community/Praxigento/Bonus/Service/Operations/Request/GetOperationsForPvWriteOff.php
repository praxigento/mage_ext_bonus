<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Service_Operations_Request_GetOperationsForPvWriteOff
    extends Praxigento_Bonus_Service_Base_Request {
    private $_logCalcId;
    private $_periodCode;
    private $_periodValue;

    /**
     * @return mixed
     */
    public function getPeriodValue() {
        return $this->_periodValue;
    }

    /**
     * @param mixed $val
     */
    public function setPeriodValue($val) {
        $this->_periodValue = $val;
    }

    /**
     * @return mixed
     */
    public function getPeriodCode() {
        return $this->_periodCode;
    }

    /**
     * @param mixed $val
     */
    public function setPeriodCode($val) {
        $this->_periodCode = $val;
    }

    /**
     * @return mixed
     */
    public function getLogCalcId() {
        return $this->_logCalcId;
    }

    /**
     * @param mixed $val
     */
    public function setLogCalcId($val) {
        $this->_logCalcId = $val;
    }
}