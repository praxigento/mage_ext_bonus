<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Model_Own_Service_Registry_Request_SaveRetailBonus as SaveRetailBonusRequest;
use Praxigento_Bonus_Model_Own_Service_Registry_Response_SaveRetailBonus as SaveRetailBonusResponse;

/**
 * Service to register bonus values in DB.
 *
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Model_Own_Service_Registry_Call
    extends Praxigento_Bonus_Model_Own_Service_Base_Call
{
    public function saveRetailBonus(SaveRetailBonusRequest $req)
    {
        $result = Mage::getModel('prxgt_bonus_model/own_service_registry_response_saveRetailBonus');
        /**
         * Prepare processing data.
         */
        /** @var  $order Mage_Sales_Model_Order */
        $order = $req->getOrder();
        $orderId = $order->getId();
        $customerId = $order->getCustomerId();
        /** @var  $customer Mage_Customer_Model_Customer */
        $customer = Mage::getModel('customer/customer')->load($customerId);
        /** @var  $upline Mage_Customer_Model_Customer */
        $upline = Mage::helper('prxgt_bonus_helper')->getUplineForCustomer($customer);
        if (!$upline || !$upline->getId()) {
            /* customer still has no upline customer, try to extract it from session */
            $upline = Mage::helper('prxgt_bonus_helper')->getUplineFromSession();
        }
        if ($upline && $upline->getId()) {
            $uplineId = $upline->getId();
            /**
             * Calculate bonus value.
             */
            $call = Mage::getModel('prxgt_bonus_model/own_service_replica_call');
            $reqRep = Mage::getModel('prxgt_bonus_model/own_service_replica_request_createQuoteFromOrder');
            $reqRep->setCustomer($upline);
            $reqRep->setOrder($order);
            $respRep = $call->createQuoteFromOrder($reqRep);
            $orderBaseGrandTotal = $order->getBaseGrandTotal();
            $quoteBaseGrandTotal = $respRep->getQuote()->getBaseGrandTotal();
            $bonusAmount = $orderBaseGrandTotal - $quoteBaseGrandTotal;
            $bonusCurr = $order->getBaseCurrencyCode();
            $this->_log->trace("New retail bonus ($bonusAmount $bonusCurr) based on order #$orderId is calculated for customer #$uplineId.");
            /**
             * Save bonus value.
             */
            $bonusModel = Mage::getModel('prxgt_bonus_model/own_order');
            $bonusModel->setOrderId($orderId);
            $bonusModel->setUplineId($uplineId);
            $bonusModel->setAmount($bonusAmount);
            $bonusModel->setCurrency($bonusCurr);
            $bonusModel->save();
            $bonusId = $bonusModel->getId();
            $this->_log->trace("New retail bonus is saved with ID #$bonusId.");
            $result->setBonusOrder($bonusModel);
        } else {
            /* cannot get upline customer, do nothing */
        }
        /**
         * Save bonus value.
         */
        return $result;
    }
}