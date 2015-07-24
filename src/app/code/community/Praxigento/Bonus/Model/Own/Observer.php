<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Model_Own_Observer extends Mage_Core_Model_Observer
{
    /** @var \Praxigento_Bonus_Logger */
    private $_log;

    function __construct()
    {
        $this->_log = Praxigento_Bonus_Logger::getLogger(__CLASS__);
    }

    /**
     * Create retail bonus on order placement.
     *
     * @param Varien_Event_Observer $event
     */
    public function onSalesOrderPlaceAfter(Varien_Event_Observer $event)
    {
        /** @var  $order Mage_Sales_Model_Order */
        $order = $event->getData('order');
        /** @var  $call Praxigento_Bonus_Model_Own_Service_Registry_Call */
        $call = Mage::getModel('prxgt_bonus_model/own_service_registry_call');
        $req = Mage::getModel('prxgt_bonus_model/own_service_registry_request_saveRetailBonus');
        $req->setOrder($order);
        try {
            $resp = $call->saveRetailBonus($req);
        } catch (Exception $e) {
            $orderId = $order->getId();
            $this->_log->error("Cannot create retail bonus for order #$orderId.");
        }
    }

    /**
     * Update downline log & snapshot tables.
     *
     * @param Varien_Event_Observer $event
     */
    public function onPrxgtCoreCustomerUplineChange(Varien_Event_Observer $event)
    {
        /** @var  $customer Nmmlm_Core_Model_Customer_Customer */
        $customer = $event->getCustomer();
        /** @var  $parent Nmmlm_Core_Model_Customer_Customer */
        $parent = $event->getParent();
        $customerId = $customer->getId();
        $customerPath = $customer->getNmmlmCoreMlmPath();
        $parentId = $parent->getId();
        /* save log record */
        /** @var  $log Praxigento_Bonus_Model_Own_Log_Downline */
        $log = Mage::getModel('prxgt_bonus_model/own_log_downline');
        $log->setCustomerId($customerId);
        $log->setParentId($parentId);
        $log->getResource()->save($log);
        /* update snapshot record */

    }
}