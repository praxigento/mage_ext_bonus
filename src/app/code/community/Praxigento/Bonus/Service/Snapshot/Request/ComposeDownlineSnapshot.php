<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Service_Snapshot_Request_ComposeDownlineSnapshot
    extends Praxigento_Bonus_Service_Base_Request {
    /** @var  string YYYYMMDD, YYYYMM, YYYY */
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
}