<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */

/**
 * Module's utilities.
 *
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Helper_Data extends Mage_Core_Helper_Abstract
{

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


}