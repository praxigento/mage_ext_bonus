<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Model_Own_Observer extends Mage_Core_Model_Observer {
    /** @var \Praxigento_Bonus_Logger */
    private $_log;

    function __construct() {
        $this->_log = Praxigento_Bonus_Logger::getLogger(__CLASS__);
    }

    /**
     * Create retail bonus on order placement.
     *
     * @param Varien_Event_Observer $event
     */
    public function onSalesOrderPlaceAfter(Varien_Event_Observer $event) {
        /** @var  $order Mage_Sales_Model_Order */
        $order = $event->getData('order');
        /** @var  $call Praxigento_Bonus_Model_Own_Service_Registry_Call */
        $call = Mage::getModel('prxgt_bonus_model/service_registry_call');
        $req = Mage::getModel('prxgt_bonus_model/service_registry_request_saveRetailBonus');
        $req->setOrder($order);
        try {
            $resp = $call->saveRetailBonus($req);
        } catch(Exception $e) {
            $orderId = $order->getId();
            $this->_log->error("Cannot create retail bonus for order #$orderId.");
        }
    }

    /**
     * Update downline log & snapshot tables.
     *
     * @param Varien_Event_Observer $event
     */
    public function onPrxgtCoreCustomerUplineChange(Varien_Event_Observer $event) {
        /** @var  $parent Nmmlm_Core_Model_Customer_Customer */
        $parent = $event->getParent();
        $parentId = $parent->getId();
        /* dont' process if parent is missed - this is first save, w/o parent data */
        if($parentId) {
            /** @var  $customer Nmmlm_Core_Model_Customer_Customer */
            $customer = $event->getCustomer();
            $customerId = $customer->getId();
            $customerPath = $customer->getNmmlmCoreMlmPath();
            $customerDepth = $customer->getNmmlmCoreMlmDepth();
            /**
             * Save log record.
             */
            /** @var  $log Praxigento_Bonus_Model_Own_Log_Downline */
            $log = Mage::getModel('prxgt_bonus_model/log_downline');
            $log->setCustomerId($customerId);
            $log->setParentId($parentId);
            $log->getResource()->save($log);
            /**
             * Update snapshot record.
             */
            /** @var  $snap Praxigento_Bonus_Model_Own_Snap_Downline */
            $snap = Mage::getModel('prxgt_bonus_model/snap_downline')->load($customerId);
            $snap->setCustomerId($customerId);
            $snap->setParentId($parentId);
            $snap->setPeriod(Config::PERIOD_KEY_NOW);
            $snap->setPath($customerPath);
            $snap->setDepth($customerDepth);
            $snap->getResource()->save($snap);
        }
    }
}