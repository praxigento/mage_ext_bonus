<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Service_Snapshot_Response_ComposeDownlineSnapshot
    extends Praxigento_Bonus_Service_Base_Response {
    /**
     * @var string 20150601 - day only period values can be in response.
     */
    private $_periodValue;

    /**
     * @return string
     */
    public function getPeriodValue() {
        return $this->_periodValue;
    }

    /**
     * @param string $val
     */
    public function setPeriodValue($val) {
        $this->_periodValue = $val;
    }

    public function isSucceed() {
        $result = ($this->getErrorCode() == self::ERR_NO_ERROR);
        return $result;
    }
}