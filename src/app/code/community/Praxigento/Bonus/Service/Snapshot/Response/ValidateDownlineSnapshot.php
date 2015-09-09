<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Service_Snapshot_Response_ValidateDownlineSnapshot
    extends Praxigento_Bonus_Service_Base_Response {

    /** @var array */
    private $_allOrphans = array();
    /** @var array */
    private $_allWrongPaths = array();
    /** @var  int */
    private $_maxDepth = 0;
    /** @var  int */
    private $_totalCustomers = 0;
    /** @var  int */
    private $_totalOrphans = 0;
    /** @var  int */
    private $_totalRoots = 0;
    /** @var  int */
    private $_totalWrongPaths = 0;

    /**
     * @return int
     */
    public function getTotalRoots() {
        return $this->_totalRoots;
    }

    /**
     * @param int $val
     */
    public function setTotalRoots($val) {
        $this->_totalRoots = $val;
    }

    /**
     * @return array
     */
    public function getAllOrphans() {
        return $this->_allOrphans;
    }

    /**
     * @param array $val
     */
    public function setAllOrphans($val) {
        $this->_allOrphans = $val;
    }

    /**
     * @return array
     */
    public function getAllWrongPaths() {
        return $this->_allWrongPaths;
    }

    /**
     * @param array $val
     */
    public function setAllWrongPaths($val) {
        $this->_allWrongPaths = $val;
    }

    /**
     * @return int
     */
    public function getMaxDepth() {
        return $this->_maxDepth;
    }

    /**
     * @param int $val
     */
    public function setMaxDepth($val) {
        $this->_maxDepth = $val;
    }

    /**
     * @return int
     */
    public function getTotalCustomers() {
        return $this->_totalCustomers;
    }

    /**
     * @param int $val
     */
    public function setTotalCustomers($val) {
        $this->_totalCustomers = $val;
    }

    /**
     * @return int
     */
    public function getTotalOrphans() {
        return $this->_totalOrphans;
    }

    /**
     * @param int $val
     */
    public function setTotalOrphans($val) {
        $this->_totalOrphans = $val;
    }

    /**
     * @return int
     */
    public function getTotalWrongPaths() {
        return $this->_totalWrongPaths;
    }

    /**
     * @param int $val
     */
    public function setTotalWrongPaths($val) {
        $this->_totalWrongPaths = $val;
    }

    public function isSucceed() {
        $result = ($this->getErrorCode() == self::ERR_NO_ERROR);
        return $result;
    }
}