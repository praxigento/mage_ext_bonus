<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Service_Operations_Request_UpdateBalance
    extends Praxigento_Bonus_Service_Base_Request {
    /** @var  int */
    private $_accountId;
    /** @var string 20150601 | 201506 | 2015 | NOW */
    private $_period = Praxigento_Bonus_Config::PERIOD_KEY_NOW;
    /** @var  decimal */
    private $_value;

    /**
     * @return int
     */
    public function getAccountId() {
        return $this->_accountId;
    }

    /**
     * @param int $val
     */
    public function setAccountId($val) {
        $this->_accountId = $val;
    }

    /**
     * @return string
     */
    public function getPeriod() {
        return $this->_period;
    }

    /**
     * @param string $val
     */
    public function setPeriod($val) {
        $this->_period = $val;
    }

    /**
     * @return decimal
     */
    public function getValue() {
        return $this->_value;
    }

    /**
     * @param decimal $val
     */
    public function setValue($val) {
        $this->_value = $val;
    }
}