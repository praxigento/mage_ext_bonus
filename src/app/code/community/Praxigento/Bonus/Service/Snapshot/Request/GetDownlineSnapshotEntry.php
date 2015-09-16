<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Service_Snapshot_Request_GetDownlineSnapshotEntry
    extends Praxigento_Bonus_Service_Base_Request {
    /** @var  int Magento ID */
    private $_customerId;
    /** @var  string YYYYMMDD, YYYYMM, YYYY */
    private $_periodValue;

    /**
     * @return mixed
     */
    public function getCustomerId() {
        return $this->_customerId;
    }

    /**
     * @param mixed $val
     */
    public function setCustomerId($val) {
        $this->_customerId = $val;
    }

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