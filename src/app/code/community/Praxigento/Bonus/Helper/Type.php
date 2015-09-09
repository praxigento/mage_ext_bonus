<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;

/**
 * Types related utilities (assets, operations, periods, ...).
 *
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Helper_Type {
    /** @var  array of assets types; 'code' is the key. */
    private $_cacheAssetTypes;
    /** @var  array of calculations types; 'code' is the key. */
    private $_cacheCalcTypes;
    /** @var  array of operations types; 'code' is the key. */
    private $_cacheOperationTypes;
    /** @var  array of calculation periods types; 'code' is the key. */
    private $_cachePeriodTypes;

    /**
     * @param $code
     *
     * @return int
     */
    public function getAssetId($code) {
        $type = $this->getAsset($code);
        $result = $type->getId();
        return $result;
    }

    /**
     * @param $code
     *
     * @return Praxigento_Bonus_Model_Own_Type_Asset
     */
    public function getAsset($code) {
        if(is_null($this->_cacheAssetTypes)) {
            $allTypes = Config::get()->collectionTypeAsset();
            $types = array();
            /** @var  $one Praxigento_Bonus_Model_Own_Type_Asset */
            foreach($allTypes as $one) {
                $types[ $one->getCode() ] = $one;
            }
            $this->_cacheAssetTypes = $types;
        }
        $result = $this->_cacheAssetTypes[ $code ];
        return $result;
    }

    /**
     * @param $code
     *
     * @return int
     */
    public function getCalcId($code) {
        $type = $this->getCalc($code);
        $result = $type->getId();
        return $result;
    }

    /**
     * @param $code
     *
     * @return Praxigento_Bonus_Model_Own_Type_Calc
     */
    public function getCalc($code) {
        if(is_null($this->_cacheCalcTypes)) {
            $allTypes = Config::get()->collectionTypeCalc();
            $types = array();
            /** @var  $one Praxigento_Bonus_Model_Own_Type_Calc */
            foreach($allTypes as $one) {
                $types[ $one->getCode() ] = $one;
            }
            $this->_cacheCalcTypes = $types;
        }
        $result = $this->_cacheCalcTypes[ $code ];
        return $result;
    }

    /**
     * @param $code
     *
     * @return int
     */
    public function getOperId($code) {
        $type = $this->getOper($code);
        $result = $type->getId();
        return $result;
    }

    /**
     * @param $code
     *
     * @return Praxigento_Bonus_Model_Own_Type_Oper
     */
    public function getOper($code) {
        if(is_null($this->_cacheOperationTypes)) {
            $allTypes = Config::get()->collectionTypeOper();
            $types = array();
            /** @var  $one Praxigento_Bonus_Model_Own_Type_Operation */
            foreach($allTypes as $one) {
                $types[ $one->getCode() ] = $one;
            }
            $this->_cacheOperationTypes = $types;
        }
        $result = $this->_cacheOperationTypes[ $code ];
        return $result;
    }

    /**
     * @param $code
     *
     * @return int
     */
    public function getPeriodId($code) {
        $type = $this->getPeriod($code);
        $result = $type->getId();
        return $result;
    }

    /**
     * @param $code
     *
     * @return Praxigento_Bonus_Model_Own_Type_Period
     */
    public function getPeriod($code) {
        if(is_null($this->_cachePeriodTypes)) {
            $allTypes = Config::get()->collectionTypePeriod();
            $types = array();
            /** @var  $one Praxigento_Bonus_Model_Own_Type_Period */
            foreach($allTypes as $one) {
                $types[ $one->getCode() ] = $one;
            }
            $this->_cachePeriodTypes = $types;
        }
        $result = $this->_cachePeriodTypes[ $code ];
        return $result;
    }

    /**
     * Return array of operation types ids that are processed in PV Write Off calculation.
     * @return array
     */
    public function getOperIdsForPersonalBonus() {
        $result = array();
        $result[] = $this->getOperId(Config::OPER_ORDER_PV);
        $result[] = $this->getOperId(Config::OPER_PV_INT);
        $result[] = $this->getOperId(Config::OPER_PV_FWRD);
        return $result;
    }

    /**
     * Return array of operation types ids that are processed in PV Write Off calculation.
     * @return array
     */
    public function getOperIdsForPvWriteOff() {
        $result = array();
        $result[] = $this->getOperId(Config::OPER_ORDER_PV);
        $result[] = $this->getOperId(Config::OPER_PV_INT);
        $result[] = $this->getOperId(Config::OPER_PV_FWRD);
        return $result;
    }
}