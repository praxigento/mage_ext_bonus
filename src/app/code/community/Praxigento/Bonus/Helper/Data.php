<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */

/**
 * Un-categorized module's utilities (access to System/Configuration parameters, etc.).
 *
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Helper_Data extends Mage_Core_Helper_Abstract
{

    public function formatAmount($value, $decimal = '.', $group = '')
    {
        return number_format($value, 2, $decimal, $group);
    }

    /**
     * Load upline customer model for the given customer.
     *
     * @param Mage_Customer_Model_Customer $customer
     * @return Mage_Customer_Model_Customer
     */
    public function getUplineForCustomer(Mage_Customer_Model_Customer $customer)
    {
        $uplineMlmId = $customer->getNmmlmCoreMlmUpline();
        /** @var  $result Mage_Customer_Model_Customer */
        $result = Nmmlm_Core_Util::findCustomerByMlmId($uplineMlmId);
        return $result;
    }

    /**
     * Return GMT DateTime instance in case of $format is null or formatted string otherwise.
     * @param null $format
     * @return DateTime|string
     */
    public function getDateGmtNow($format = null)
    {
        $result = Nmmlm_Core_Util::dateGmtNow($format);
        return $result;
    }

    /**
     * Extract upline customer data from session.
     *
     * @return Mage_Customer_Model_Customer|null
     */
    public function getUplineFromSession()
    {
        $proc = Mage::getSingleton('nmmlm_core_model/own_referral_customer_processor');
        $result = $proc->sessionGetUpline();
        return $result;
    }

    public function cfgGeneralDownlineDepth($store = null)
    {
        $result = Mage::getStoreConfig('prxgt_bonus/general/downline_depth', $store);
        $result *= 1;
        $result = $result < 1 ? 1 : $result;
        return $result;
    }

    public function cfgPersonalBonusEnabled($store = null)
    {
        $result = Mage::getStoreConfig('prxgt_bonus/personal_bonus/is_enabled', $store);
        $result = filter_var($result, FILTER_VALIDATE_BOOLEAN);
        return $result;
    }

    public function cfgPersonalBonusPeriod($store = null)
    {
        $result = Mage::getStoreConfig('prxgt_bonus/personal_bonus/period', $store);
        $result = ($result) ? $result : Praxigento_Bonus_Config::PERIOD_DAY;
        return $result;
    }

    public function cfgPersonalBonusWeekLastDay($store = null)
    {
        $result = Mage::getStoreConfig('prxgt_bonus/personal_bonus/period_last_day', $store);
        $result = ($result) ? $result : Praxigento_Bonus_Model_Own_Source_Weekday::FRIDAY;
        return $result;
    }

    public function cfgPersonalBonusPayoutDelay($store = null)
    {
        $result = Mage::getStoreConfig('prxgt_bonus/personal_bonus/payout_delay', $store);
        $result = filter_var($result * 1, FILTER_VALIDATE_INT);
        return $result;
    }


    public function cfgRetailBonusEnabled($store = null)
    {
        $result = Mage::getStoreConfig('prxgt_bonus/retail_bonus/is_enabled', $store);
        $result = filter_var($result, FILTER_VALIDATE_BOOLEAN);
        return $result;
    }

    public function cfgRetailBonusFeeFixed($store = null)
    {
        $result = Nmmlm_Core_Config::cfgReferralsRetailBonusFeeFixed($store);
        return $result;
    }

    public function cfgRetailBonusFeeMax($store = null)
    {
        $result = Nmmlm_Core_Config::cfgReferralsRetailBonusFeeMax($store);
        return $result;
    }

    public function cfgRetailBonusFeeMin($store = null)
    {
        $result = Nmmlm_Core_Config::cfgReferralsRetailBonusFeeMin($store);
        return $result;
    }

    public function cfgRetailBonusFeePercent($store = null)
    {
        $result = Nmmlm_Core_Config::cfgReferralsRetailBonusFeePercent($store);
        return $result;
    }
}