<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Service_Operations_Request_CreateTransaction
    extends Praxigento_Bonus_Service_Base_Request {
    /** @var  int */
    private $_creditAccId;
    /** @var  string 'YYYY-MM-DD HH:MM:SS' */
    private $_dateApplied;
    /** @var  int */
    private $_debitAccId;
    /** @var  int */
    private $_operationId;
    /** @var  decimal */
    private $_value;

    /**
     * @return int
     */
    public function getCreditAccId() {
        return $this->_creditAccId;
    }

    /**
     * @param int $val
     */
    public function setCreditAccId($val) {
        $this->_creditAccId = $val;
    }

    /**
     * @return string
     */
    public function getDateApplied() {
        return $this->_dateApplied;
    }

    /**
     * @param string $val
     */
    public function setDateApplied($val) {
        $this->_dateApplied = $val;
    }

    /**
     * @return int
     */
    public function getDebitAccId() {
        return $this->_debitAccId;
    }

    /**
     * @param int $val
     */
    public function setDebitAccId($val) {
        $this->_debitAccId = $val;
    }

    /**
     * @return int
     */
    public function getOperationId() {
        return $this->_operationId;
    }

    /**
     * @param int $val
     */
    public function setOperationId($val) {
        $this->_operationId = $val;
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