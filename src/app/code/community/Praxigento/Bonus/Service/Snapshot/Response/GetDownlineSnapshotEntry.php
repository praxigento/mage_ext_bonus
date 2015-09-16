<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Service_Snapshot_Response_GetDownlineSnapshotEntry
    extends Praxigento_Bonus_Service_Base_Response {
    const ERR_SNAP_IS_NOT_FOUND = 'SNAP_IS_NOT_FOUND';
    /** @var  int Magento ID of the customer */
    private $_customerId;
    /** @var  int  depth of the customer relative to the tree root */
    private $_depth;
    /** @var  int Magento ID of the parent customer */
    private $_parentId;
    /** @var  string path to the customer in the tree (/123/456/789/) */
    private $_path;
    /** @var  string exact value for the period in the snapshot that is found (20160630 | NOW) */
    private $_periodExact;
    /** @var  string requested period value (2016 | 201606 | 20160601 | NOW) */
    private $_periodRequested;

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
    public function getDepth() {
        return $this->_depth;
    }

    /**
     * @param int $val
     */
    public function setDepth($val) {
        $this->_depth = $val;
    }

    /**
     * @return int
     */
    public function getParentId() {
        return $this->_parentId;
    }

    /**
     * @param int $val
     */
    public function setParentId($val) {
        $this->_parentId = $val;
    }

    /**
     * @return string
     */
    public function getPath() {
        return $this->_path;
    }

    /**
     * @param string $val
     */
    public function setPath($val) {
        $this->_path = $val;
    }

    /**
     * @return string
     */
    public function getPeriodExact() {
        return $this->_periodExact;
    }

    /**
     * @param string $val
     */
    public function setPeriodExact($val) {
        $this->_periodExact = $val;
    }

    /**
     * @return string
     */
    public function getPeriodRequested() {
        return $this->_periodRequested;
    }

    /**
     * @param string $val
     */
    public function setPeriodRequested($val) {
        $this->_periodRequested = $val;
    }


    public function isSucceed() {
        $result = ($this->getErrorCode() == self::ERR_NO_ERROR);
        return $result;
    }
}