<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Block_Adminhtml_Own_Customer_Downline_Validation_Result
    extends Praxigento_Bonus_Block_Adminhtml_Own_Base {

    /** @var  int */
    private $_maxDepth = 0;
    /** @var  string */
    private $_periodValue;
    /** @var  int */
    private $_totalCustomers = 0;
    /** @var  int */
    private $_totalOrphans = 0;
    /** @var  int */
    private $_totalRoots = 0;
    /** @var  int */
    private $_totalWrongPaths = 0;

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
    public function getTotalRoots() {
        return $this->_totalRoots;
    }

    /**
     * @param int $val
     */
    public function setTotalRoots($val) {
        $this->_totalRoots = $val;
    }

    public function uiTitle() {
        echo $this->__('Downline Tree Validation Result');
    }
}