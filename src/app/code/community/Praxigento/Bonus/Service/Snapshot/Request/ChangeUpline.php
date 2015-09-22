<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Service_Snapshot_Request_ChangeUpline
    extends Praxigento_Bonus_Service_Base_Request {
    /** @var  int Magento ID for customer to be changed. */
    private $_customerId;
    /** @var  int Magento ID of the new upline customer. */
    private $_newUplineId;

    /**
     * @return int
     */
    public function getCustomerId() {
        return $this->_customerId;
    }

    /**
     * @param int $val
     */
    public function setCustomerId($val) {
        $this->_customerId = $val;
    }

    /**
     * @return int
     */
    public function getNewUplineId() {
        return $this->_newUplineId;
    }

    /**
     * @param int $val
     */
    public function setNewUplineId($val) {
        $this->_newUplineId = $val;
    }

}