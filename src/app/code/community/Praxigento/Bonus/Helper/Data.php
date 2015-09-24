<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Nmmlm_Core_Config as ConfigCore;
use Praxigento_Bonus_Config as Config;
use Praxigento_Bonus_Model_Own_Snap_Downline as SnapDownline;

/**
 * Un-categorized module's utilities (access to System/Configuration parameters, etc.).
 *
 * This class also separates Core Helper methods from this module. We can use core helper (Nmmlm_Core_Helper_Data)
 * and we will have a code dependency to core class, or we can create intermediary methods in this helper
 * (less dependency).
 *
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Helper_Data extends Mage_Core_Helper_Abstract {
    /**
     * @var Nmmlm_Core_Helper_Data
     */
    private $_helperCore;
    /** @var  Varien_Db_Adapter_Interface */
    private $conn;

    function __construct() {
        $this->_helperCore = Config::get()->helperCore();
        $this->conn = Config::get()->connectionWrite();
    }


    public function formatAmount($value, $decimal = '.', $group = '') {
        $result = $this->_helperCore->formatAmount($value, $decimal, $group);
        return $result;
    }

    /**
     * Load upline customer model for the given customer.
     *
     * @param        $custId - Magento entity ID
     * @param string $period - '20150601'
     * @param string $attrs - EAV attributes to load (default - '*', use array() of attribute names)
     *
     * @return Nmmlm_Core_Model_Customer_Customer
     */
    public function getUplineForCustomer($custId, $period = Config::PERIOD_KEY_NOW, $attrs = '*') {
        /* prepare table aliases and models */
        $as = 'snap';
        $eType = Config::ENTITY_SNAP_DOWNLINE;
        $tblSnapDwnl = Config::get()->tableName($eType, $as);
        /** @var  $query Varien_Db_Select */
        $query = $this->conn->select();
        $cols = array( SnapDownline::ATTR_PARENT_ID );
        $query->from($tblSnapDwnl, $cols);
        $query->where($as . '.' . SnapDownline::ATTR_CUSTOMER_ID . '=:custId');
        $query->where($as . '.' . SnapDownline::ATTR_PERIOD . '=:period');
        $sql = (string)$query;
        $parentId = $this->conn->fetchOne($query, array( 'custId' => $custId, 'period' => $period ));
        $result = $this->_helperCore->getCustomerById($parentId, $attrs);
        return $result;
    }

    /**
     * Return GMT DateTime instance in case of $format is null or formatted string otherwise.
     *
     * @param null $format
     *
     * @return DateTime|string
     */
    public function getDateGmtNow($format = null) {
        $result = $this->_helperCore->dateGmtNow($format);
        return $result;
    }

    /**
     * Extract upline customer data from session.
     *
     * @return Mage_Customer_Model_Customer|null
     */
    public function getUplineFromSession() {
        $proc = Config::get()->singleton('nmmlm_core_model/own_referral_customer_processor');
        $result = $proc->sessionGetUpline();
        return $result;
    }

    public function cfgGeneralAccountantMlmId($store = null) {
        $result = Mage::getStoreConfig('prxgt_bonus/general/accountant_mlmid', $store);
        $result = ($result) ? $result : 'Please type MLM ID for accountant customer in System / Configuration / Praxigento / Bonus Calculation / General / MLM ID for Accountant Customer';
        return $result;
    }

    public function cfgGeneralDownlineDepth($store = null) {
        $result = Mage::getStoreConfig('prxgt_bonus/general/downline_depth', $store);
        $result *= 1;
        $result = $result < 1 ? 1 : $result;
        return $result;
    }

    public function cfgPersonalBonusEnabled($store = null) {
        $result = Mage::getStoreConfig('prxgt_bonus/personal_bonus/is_enabled', $store);
        $result = filter_var($result, FILTER_VALIDATE_BOOLEAN);
        return $result;
    }

    public function cfgPersonalBonusPeriod($store = null) {
        $result = Mage::getStoreConfig('prxgt_bonus/personal_bonus/period', $store);
        $result = ($result) ? $result : Praxigento_Bonus_Config::PERIOD_DAY;
        return $result;
    }

    public function cfgPersonalBonusWeekLastDay($store = null) {
        $result = Mage::getStoreConfig('prxgt_bonus/personal_bonus/period_last_day', $store);
        $result = ($result) ? $result : Praxigento_Bonus_Model_Own_Source_Weekday::FRIDAY;
        return $result;
    }

    public function cfgPersonalBonusPayoutDelay($store = null) {
        $result = Mage::getStoreConfig('prxgt_bonus/personal_bonus/payout_delay', $store);
        $result = filter_var($result * 1, FILTER_VALIDATE_INT);
        return $result;
    }


    public function cfgRetailBonusEnabled($store = null) {
        $result = Mage::getStoreConfig('prxgt_bonus/retail_bonus/is_enabled', $store);
        $result = filter_var($result, FILTER_VALIDATE_BOOLEAN);
        return $result;
    }

    public function cfgRetailBonusFeeFixed($store = null) {
        $result = Nmmlm_Core_Config::cfgReferralsRetailBonusFeeFixed($store);
        return $result;
    }

    public function cfgRetailBonusFeeMax($store = null) {
        $result = Nmmlm_Core_Config::cfgReferralsRetailBonusFeeMax($store);
        return $result;
    }

    public function cfgRetailBonusFeeMin($store = null) {
        $result = Nmmlm_Core_Config::cfgReferralsRetailBonusFeeMin($store);
        return $result;
    }

    public function cfgRetailBonusFeePercent($store = null) {
        $result = Nmmlm_Core_Config::cfgReferralsRetailBonusFeePercent($store);
        return $result;
    }
}