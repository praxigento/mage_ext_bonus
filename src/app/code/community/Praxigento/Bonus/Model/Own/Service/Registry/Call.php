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
        if ($this->_helper->cfgRetailBonusEnabled()) {
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
            $upline = $this->_helper->getUplineForCustomer($customer);
            if (!$upline || !$upline->getId()) {
                /* customer still has no upline customer, try to extract it from session */
                $upline = $this->_helper->getUplineFromSession();
            }
            if ($upline && $upline->getId()) {
                $uplineId = $upline->getId();
                $bonusCurr = $order->getBaseCurrencyCode();
                /**
                 * Compose upline's quote from customer order.
                 */
                $call = Mage::getModel('prxgt_bonus_model/own_service_replica_call');
                $reqRep = Mage::getModel('prxgt_bonus_model/own_service_replica_request_createQuoteFromOrder');
                $reqRep->setCustomer($upline);
                $reqRep->setOrder($order);
                $respRep = $call->createQuoteFromOrder($reqRep);
                $quote = $respRep->getQuote();
                /**
                 * Calculate bonus value.
                 */
                /* customer order */
                $orderBaseShipping = $order->getBaseShippingAmount();
                $orderBaseTax = $order->getBaseTaxAmount();
                $orderBaseGrandTotal = $order->getBaseGrandTotal();
                $orderAmount = $orderBaseGrandTotal - $orderBaseTax - $orderBaseShipping;
                $this->_log->trace("Order #$orderId amount to calculate retail bonus: $orderAmount $bonusCurr ($orderBaseGrandTotal - $orderBaseTax - $orderBaseShipping [grand - tax - shipping]).");
                /* upline quote */
                $quoteBaseShipping = $quote->getBaseShipping();
                $quoteBaseTax = $quote->getBaseTax();
                $quoteBaseGrandTotal = $quote->getBaseGrandTotal();
                $quoteAmount = $quoteBaseGrandTotal - $quoteBaseTax - $quoteBaseShipping;
                $this->_log->trace("Quote for order #$orderId amount to calculate retail bonus: $quoteAmount $bonusCurr ($quoteBaseGrandTotal - $quoteBaseTax - $quoteBaseShipping [grand - tax - shipping]).");
                /* bonus */
                $bonusAmount = $orderAmount - $quoteAmount;
                $bonusFee = $this->_calcRetailBonusFee($bonusAmount);
                $bonusFinal = $bonusAmount - $bonusFee;
                $this->_log->trace("New retail bonus ($bonusFinal $bonusCurr) based on order #$orderId is calculated for customer #$uplineId.");
                /**
                 * Save bonus value.
                 */
                $bonusModel = Mage::getModel('prxgt_bonus_model/own_order');
                $bonusModel->setOrderId($orderId);
                $bonusModel->setUplineId($uplineId);
                $bonusModel->setCurrency($bonusCurr);
                $bonusModel->setAmount($bonusFinal);
                $bonusModel->setFee($bonusFee);
                $bonusModel->setFeeFixed($this->_helper->cfgRetailBonusFeeFixed());
                $bonusModel->setFeePercent($this->_helper->cfgRetailBonusFeePercent());
                $bonusModel->setFeeMin($this->_helper->cfgRetailBonusFeeMin());
                $bonusModel->setFeeMax($this->_helper->cfgRetailBonusFeeMax());
                $bonusModel->save();
                $bonusId = $bonusModel->getId();
                $this->_log->trace("New retail bonus is saved with ID #$bonusId.");
                $result->setBonusOrder($bonusModel);
                $result->setErrorCode(SaveRetailBonusResponse::ERR_NO_ERROR);
            } else {
                /* cannot get upline customer, do nothing */
            }
        } else {
            /* retail bonus is disabled */
            $result->setErrorCode(SaveRetailBonusResponse::ERR_BONUS_DISABLED);
        }
        return $result;
    }

    private function _calcRetailBonusFee($amount)
    {
        $fixed = $this->_helper->cfgRetailBonusFeeFixed();
        $percent = $this->_helper->cfgRetailBonusFeePercent();
        $min = $this->_helper->cfgRetailBonusFeeMin();
        $max = $this->_helper->cfgRetailBonusFeeMax();
        $result = $fixed + $amount * $percent;
        $result = ($result < $min) ? $min : $result;
        $result = ($result > $max) ? $max : $result;
        $result = number_format($result, 2);
        $this->_log->trace("Retail bonus fee for amount $amount is $result ($min < [$fixed + $amount * $percent] < $max).");
        return $result;
    }

    private function _calcRetailBonusAmount()
    {
        /* calculate retail order amount w/o tax and shipping */
        /** @var  $order Mage_Sales_Model_Order */
        $order = $this->_wOrder->getObj();
        $retailShipping = $order->getBaseShippingAmount();
        $retailTax = $order->getBaseTaxAmount();
        $retailGrand = $order->getBaseGrandTotal();
        $retailAmount = $retailGrand - $retailTax - $retailShipping;
        /* calculate upline quote  amount w/o tax and shipping */
        /** @var  array */
        $quoteBaseTotals = $this->_quote->getShippingAddress()->getAllBaseTotalAmounts();
        $uplineShipping = $quoteBaseTotals['shipping'];
        $uplineTax = $quoteBaseTotals['tax'];
        $uplineGrand = $this->_quote->getBaseGrandTotal();
        $uplineAmount = $uplineGrand - $uplineTax - $uplineShipping;
        /* calculate retail bonus amount and reduce it on processing fee value */
        $result = $retailAmount - $uplineAmount;
        $fee = $this->_calculateFee($result);
        $this->_log->debug("Retail bonus amount: $result; retail bonus processing fee: $fee.");
        $result -= $fee;
        $result = number_format($result, 2);
        $this->_log->debug("Final retail bonus amount: $result. Subtotals with discount for order amount: $retailAmount, upline order amount: $uplineAmount; processing fee: $fee.");
        $this->_bonusAmount = $result;
        return $result;
    }
}