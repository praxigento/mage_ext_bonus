<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;
use Praxigento_Bonus_Model_Own_Order as BonusOrder;
use Praxigento_Bonus_Model_Own_Payout as Payout;
use Praxigento_Bonus_Model_Own_Payout_Transact as PayoutTransact;
use Praxigento_Bonus_Model_Own_Service_Base_Response as BaseResponse;
use Praxigento_Bonus_Model_Own_Service_Registry_Request_CreatePayments as CreatePaymentsRequest;
use Praxigento_Bonus_Model_Own_Service_Registry_Request_CreatePayouts as CreatePayoutsRequest;
use Praxigento_Bonus_Model_Own_Service_Registry_Request_CreateTransactions as CreateTransactionsRequest;
use Praxigento_Bonus_Model_Own_Service_Registry_Request_GetUnprocessedBonusesCount as GetUnprocessedBonusesCountRequest;
use Praxigento_Bonus_Model_Own_Service_Registry_Request_GetUnprocessedPayoutsCount as GetUnprocessedPayoutsCountRequest;
use Praxigento_Bonus_Model_Own_Service_Registry_Request_GetUnprocessedTransactionsCount as GetUnprocessedTransactionsCountRequest;
use Praxigento_Bonus_Model_Own_Service_Registry_Request_SaveRetailBonus as SaveRetailBonusRequest;
use Praxigento_Bonus_Model_Own_Service_Registry_Response_CreatePayments as CreatePaymentsResponse;
use Praxigento_Bonus_Model_Own_Service_Registry_Response_CreatePayouts as CreatePayoutsResponse;
use Praxigento_Bonus_Model_Own_Service_Registry_Response_CreateTransactions as CreateTransactionsResponse;
use Praxigento_Bonus_Model_Own_Service_Registry_Response_GetUnprocessedBonusesCount as GetUnprocessedBonusesCountResponse;
use Praxigento_Bonus_Model_Own_Service_Registry_Response_GetUnprocessedPayoutsCount as GetUnprocessedPayoutsCountResponse;
use Praxigento_Bonus_Model_Own_Service_Registry_Response_GetUnprocessedTransactionsCount as GetUnprocessedTransactionsCountResponse;
use Praxigento_Bonus_Model_Own_Service_Registry_Response_SaveRetailBonus as SaveRetailBonusResponse;
use Praxigento_Bonus_Model_Own_Transact as Transact;

/**
 * Service to register bonus values in DB.
 *
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Model_Own_Service_Registry_Call
    extends Praxigento_Bonus_Model_Own_Service_Base_Call
{
    /* aliases used in selects and result sets */
    const AS_AMOUNT_BONUS = 'amountBonus';
    const AS_AMOUNT_FEE = 'amountFee';
    const AS_CURR = 'currency';
    const AS_CUSTOMER_ID = 'customerId';
    const AS_DATE_CREATED = 'dateCreated';
    const AS_ID = 'id';
    const AS_ORDER_ID = 'orderId';
    const AS_REF = 'ref';

    public function getUnprocessedBonusesCount(GetUnprocessedBonusesCountRequest $req)
    {
        /** @var  $result GetUnprocessedBonusesCountResponse */
        $result = Mage::getModel('prxgt_bonus_model/own_service_registry_response_getUnprocessedBonusesCount');
        $data = $this->_readUnprocessedBonuses();
        $count = count($data);
        $result->setCount($count);
        return $result;
    }

    /**
     * Select ids of the bonuses should be converted to transactions.
     *
     * @return array
     */
    protected function _readUnprocessedBonuses()
    {
        /** @var  $rsrc Mage_Core_Model_Resource */
        $rsrc = Mage::getSingleton('core/resource');
        /** @var  \Varien_Db_Adapter_Pdo_Mysql */
        $conn = $rsrc->getConnection('core_write');
        $tblSales = $rsrc->getTableName('sales/order');
        $tblRetail = $rsrc->getTableName(Config::CFG_MODEL . '/' . Config::CFG_ENTITY_ORDER);
        $entityId = 'entity_id';
        $id = BonusOrder::ATTR_ID;
        $orderId = BonusOrder::ATTR_ORDER_ID;
        $customerId = BonusOrder::ATTR_UPLINE_ID;
        $transactId = BonusOrder::ATTR_TRANSACT_ID;
        $amountBonus = BonusOrder::ATTR_AMOUNT;
        $amountFee = BonusOrder::ATTR_FEE;
        $currency = BonusOrder::ATTR_CURR;
        $asId = self::AS_ID;
        $asOrderId = self::AS_ORDER_ID;
        $asCustId = self::AS_CUSTOMER_ID;
        $asAmntBonus = self::AS_AMOUNT_BONUS;
        $asAmntFee = self::AS_AMOUNT_FEE;
        $asCurr = self::AS_CURR;
        $query = "
SELECT
  $tblRetail.$id AS $asId,
  $tblRetail.$orderId AS $asOrderId,
  $tblRetail.$customerId AS $asCustId,
  $tblRetail.$amountBonus AS $asAmntBonus,
  $tblRetail.$amountFee AS $asAmntFee,
  $tblRetail.$currency AS $asCurr
FROM $tblRetail
  LEFT OUTER JOIN $tblSales ON $tblRetail.$orderId=$tblSales.$entityId
WHERE
  ($tblRetail.$transactId IS NULL) AND
  (
    ($tblSales.state='processing') || ($tblSales.state='complete')
  ) AND
  ($tblRetail.$amountBonus > 0)
";
        $rs = $conn->query($query);
        $result = $rs->fetchAll();
        return $result;
    }

    public function getUnprocessedTransactionsCount(GetUnprocessedTransactionsCountRequest $req)
    {
        /** @var  $result GetUnprocessedTransactionsCountResponse */
        $result = Mage::getModel('prxgt_bonus_model/own_service_registry_response_getUnprocessedTransactionsCount');
        $data = $this->_readUnprocessedTransactions();
        $count = count($data);
        $result->setCount($count);
        return $result;
    }

    /**
     * Select ids of the transactions should be converted to payouts.
     *
     * @return array
     */
    protected function _readUnprocessedTransactions()
    {
        /** @var  $rsrc Mage_Core_Model_Resource */
        $rsrc = Mage::getSingleton('core/resource');
        /** @var  \Varien_Db_Adapter_Pdo_Mysql */
        $conn = $rsrc->getConnection('core_write');
        /* tables */
        $tblTransact = $rsrc->getTableName(Config::CFG_MODEL . '/' . Config::CFG_ENTITY_TRANSACT);
        $tblPayoutTransact = $rsrc->getTableName(Config::CFG_MODEL . '/' . Config::CFG_ENTITY_PAYOUT_TRANSACT);
        /* perform query */
        $query = "
SELECT
  pbt.*
FROM $tblTransact AS pbt
  LEFT OUTER JOIN $tblPayoutTransact AS pbpt
    ON pbt.id = pbpt.transact_id
WHERE
  (pbpt.payout_id IS NULL)
";
        $rs = $conn->query($query);
        $result = $rs->fetchAll();
        return $result;
    }

    public function getUnprocessedPayoutsCount(GetUnprocessedPayoutsCountRequest $req)
    {
        /** @var  $result GetUnprocessedPayoutsCountResponse */
        $result = Mage::getModel('prxgt_bonus_model/own_service_registry_response_getUnprocessedPayoutsCount');
        $data = $this->_readUnprocessedPayouts();
        $count = count($data);
        $result->setCount($count);
        return $result;
    }

    /**
     * Select ids of the payouts should be paid.
     *
     * @return array
     */
    protected function _readUnprocessedPayouts()
    {
        /** @var  $rsrc Mage_Core_Model_Resource */
        $rsrc = Mage::getSingleton('core/resource');
        /** @var  \Varien_Db_Adapter_Pdo_Mysql */
        $conn = $rsrc->getConnection('core_write');
        /* tables */
        $tblPayout = $rsrc->getTableName(Config::CFG_MODEL . '/' . Config::CFG_ENTITY_PAYOUT);
        /* perform query */
        $query = "
SELECT
  pbt.*
FROM $tblPayout AS pbt
WHERE
  (pbt.reference IS NULL)
";
        $rs = $conn->query($query);
        $result = $rs->fetchAll();
        return $result;
    }

    /**
     * @param Praxigento_Bonus_Model_Own_Service_Registry_Request_CreatePayments $req
     * @return Praxigento_Bonus_Model_Own_Service_Registry_Response_CreatePayments
     */
    public function createPayments(CreatePaymentsRequest $req)
    {
        /** @var  $result CreatePaymentsResponse */
        $result = Mage::getModel('prxgt_bonus_model/own_service_registry_response_createPayments');
        if ($this->_helper->cfgRetailBonusEnabled()) {
            $items = $this->_readUnprocessedPayouts();
            $count = count($items);
            $this->_log->debug("Total $count payouts should be processed to create payments.");
            if ($count) {
                $refs = array();
                $payout = Mage::getModel('prxgt_bonus_model/own_payout');
                foreach ($items as $one) {
                    $ref = $this->_createOnePayment($one);
                    if ($ref) {
                        $payout->load($one[Payout::ATTR_ID]);
                        $payout->setDatePaid($this->_helper->getDateGmtNow('Y-m-d H:i:s'));
                        $payout->setReference($ref);
                        $payout->save();
                        $refs[] = $ref;
                    }
                }
                $result->setPaymentsRefs($refs);
            }
        } else {
            /* retail bonus is disabled */
            $result->setErrorCode(BaseResponse::ERR_BONUS_DISABLED);
        }
        return $result;
    }

    protected function _createOnePayment($data)
    {
        $result = null;
        /* by default payment is not created, just return payout ID as a reference */
        if ($data[Payout::ATTR_ID]) $result = $data[Payout::ATTR_ID];
        return $result;
    }

    /**
     * @param Praxigento_Bonus_Model_Own_Service_Registry_Request_CreatePayouts $req
     * @return Praxigento_Bonus_Model_Own_Service_Registry_Response_CreatePayouts
     */
    public function createPayouts(CreatePayoutsRequest $req)
    {
        /** @var  $result CreatePayoutsResponse */
        $result = Mage::getModel('prxgt_bonus_model/own_service_registry_response_createPayouts');
        if ($this->_helper->cfgRetailBonusEnabled()) {
            $items = $this->_readUnprocessedTransactions();
            $count = count($items);
            $this->_log->debug("Total $count transactions should be processed to create payouts.");
            if ($count) {
                $byCustomer = $this->_groupTransactionsByCustomer($items);
                $idsCreated = array();
                foreach ($byCustomer as $customerId => $items) {
                    $newId = $this->_createOnePayout($items, $req->getDescription());
                    if ($newId) $idsCreated[] = $newId;
                }
                $result->setPayoutIds($idsCreated);
            }
        } else {
            /* retail bonus is disabled */
            $result->setErrorCode(BaseResponse::ERR_BONUS_DISABLED);
        }
        return $result;
    }

    protected function _groupTransactionsByCustomer($data)
    {
        $result = array();
        foreach ($data as $one) {
            $customerId = $one[Transact::ATTR_CUSTOMER_ID];
            if (!isset($result[$customerId])) {
                $result[$customerId] = array();
            }
            $result[$customerId][] = $one;
        }
        return $result;
    }

    protected function _createOnePayout($data, $desc)
    {
        $result = null;
        $payout = Mage::getModel('prxgt_bonus_model/own_payout');
        $transact = Mage::getModel('prxgt_bonus_model/own_transact');
        /* calculate payout attributes */
        /* customer id & currency should be the same for all items in $data */
        $first = reset($data);
        $customerId = $first[Transact::ATTR_CUSTOMER_ID];
        $currency = $first[Transact::ATTR_CURR];
        $amount = 0;
        $isFailed = false;
        foreach ($data as $one) {
            $oneId = $one[Transact::ATTR_ID];
            $oneCustomerId = $one[Transact::ATTR_CUSTOMER_ID];
            $oneAmount = $one[Transact::ATTR_AMOUNT];
            $oneCurr = $one[Transact::ATTR_CURR];
            $oneCreated = $one[Transact::ATTR_DATE_CREATED];
            $this->_log->trace("Collect transaction #$oneId for customer #$oneCustomerId on $oneAmount $oneCurr at $oneCreated.");
            if ($oneCustomerId != $customerId) {
                $this->_log->error("Cannot collect transaction to payout - customer is mismatched.");
                $isFailed = true;
                break;
            }
            if ($oneCurr != $currency) {
                $this->_log->error("Cannot collect transaction to payout - currency is mismatched.");
                $isFailed = true;
                break;
            }
            $amount += $oneAmount;
        }
        if (!$isFailed) {
            /* start transaction */
            $conn = Mage::getSingleton('core/resource')->getConnection('core_write');
            try {
                $conn->beginTransaction();
                /* save payout */
                $payout->setCustomerId($customerId);
                $payout->setAmount($amount);
                $payout->setCurrency($currency);
                $payout->setDateCreated($this->_helper->getDateGmtNow('Y-m-d H:i:s'));
                $payout->setDescription($desc);
                $payout->getResource()->save($payout);
                $payoutId = $payout->getId();
                /* save relations between payout and transactions */
                foreach ($data as $one) {
                    $payoutTransact = Mage::getModel('prxgt_bonus_model/own_payout_transact');
                    $payoutTransact->setPayoutId($payoutId);
                    $payoutTransact->setTransactId($one[Transact::ATTR_ID]);
                    $payoutTransact->getResource()->save($payoutTransact);
                }
                $conn->commit();
                $result = $payoutId;
            } catch (Exception $e) {
                $conn->rollback();
            }
        }
        return $result;
    }

    public function createTransactions(CreateTransactionsRequest $req)
    {
        /** @var  $result CreateTransactionsResponse */
        $result = Mage::getModel('prxgt_bonus_model/own_service_registry_response_createTransactions');
        if ($this->_helper->cfgRetailBonusEnabled()) {
            $idsCreated = array();
            $ids = $this->_readUnprocessedBonuses();
            $count = count($ids);
            $this->_log->debug("Total $count bonuses should be processed to create transactions.");
            foreach ($ids as $one) {
                $newId = $this->_createOneTransaction($one);
                if ($newId) {
                    $idsCreated[] = $newId;
                }
            }
            $result->setTransactionIds($idsCreated);
        } else {
            /* retail bonus is disabled */
            $result->setErrorCode(BaseResponse::ERR_BONUS_DISABLED);
        }
        return $result;
    }

    /**
     * Create one transaction and save new transaction id into bonus.
     *
     * @param $data
     * @return mixed
     */
    protected function _createOneTransaction($data)
    {
        $transact = Mage::getModel('prxgt_bonus_model/own_transact');
        $retail = Mage::getModel('prxgt_bonus_model/own_order');
        /* start transaction */
        $conn = Mage::getSingleton('core/resource')->getConnection('core_write');
        try {
            $conn->beginTransaction();
            /* save transaction */
            $transact->setAmount($data[self::AS_AMOUNT_BONUS]);
            $transact->setCurrency($data[self::AS_CURR]);
            $transact->setCustomerId($data[self::AS_CUSTOMER_ID]);
            $transact->setDateCreated($this->_helper->getDateGmtNow('Y-m-d H:i:s'));
            $transact->getResource()->save($transact);
            $trnId = $transact->getId();
            /* update retail bonus */
            $retail->load($data[self::AS_ID]);
            $retail->setTransactId($trnId);
            $retail->getResource()->save($retail);
            $conn->commit();
        } catch (Exception $e) {
            $conn->rollback();
        }
        $result = $transact->getId();
        return $result;
    }

    /**
     * Calculate retail bonus based on given order and save it to database.
     *
     * @param Praxigento_Bonus_Model_Own_Service_Registry_Request_SaveRetailBonus $req
     * @return Praxigento_Bonus_Model_Own_Service_Registry_Response_SaveRetailBonus
     */
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
                $orderAmount = $this->_helper->formatAmount($orderAmount);
                $this->_logRetailBonusOrder($order);
                $this->_log->trace("Order #$orderId amount to calculate retail bonus: $orderAmount $bonusCurr ($orderBaseGrandTotal - $orderBaseTax - $orderBaseShipping [grand - tax - shipping]).");
                /* upline quote */
                $quoteBaseShipping = $this->_calcRetailBonusQuoteShipping($quote);
                $quoteBaseTax = $this->_calcRetailBonusQuoteTax($quote);
                $quoteBaseGrandTotal = $quote->getBaseGrandTotal();
                $quoteAmount = $quoteBaseGrandTotal - $quoteBaseTax - $quoteBaseShipping;
                $quoteAmount = $this->_helper->formatAmount($quoteAmount);
                $this->_logRetailBonusQuote($quote);
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
            $result->setErrorCode(BaseResponse::ERR_BONUS_DISABLED);
        }
        return $result;
    }

    private function _logRetailBonusOrder(Mage_Sales_Model_Order $order)
    {
        $incId = $order->getIncrementId();
        $items = $order->getAllItems();
        $total = count($items);
        $storeId = $order->getStoreId();
        $this->_log->trace("Order #$incId; total items: $total; store: $storeId;");
        foreach ($items as $item) {
            /** @var $item Mage_Sales_Model_Order_Item */
            $itemId = $item->getId();
            $sku = $item->getSku();
            $qty = $item->getQtyOrdered();
            $price = $item->getBasePrice();
            $tax = $item->getBaseTaxAmount();
            $discount = $item->getBaseDiscountAmount();
            $rowTotal = $item->getBaseRowTotal();
            $pvUnit = $item->getData(Nmmlm_Core_Config::ATTR_COMMON_PV_UNIT);
            $pvTotal = $item->getData(Nmmlm_Core_Config::ATTR_COMMON_PV_TOTAL);
            $appliedRules = $item->getAppliedRuleIds();
            $this->_log->trace("\tItem data: ID: $itemId; SKU: $sku; Qty: $qty; Price: $price; Discount: $discount; Tax: $tax; Total: $rowTotal; PV Unit: $pvUnit; PV Total: $pvTotal; Sales Rules: $appliedRules;");
        }
    }

    protected function _calcRetailBonusQuoteShipping(Mage_Sales_Model_Quote $quote)
    {
        /** @var  $shipping */
        $shipping = $quote->getShippingAddress();
        $result = $shipping->getBaseShippingAmount();
        return $result;
    }

    protected function _calcRetailBonusQuoteTax(Mage_Sales_Model_Quote $quote)
    {
        /** @var  $shipping */
        $shipping = $quote->getShippingAddress();
        $result = $shipping->getBaseTaxAmount();
        return $result;
    }

    private function _logRetailBonusQuote(Mage_Sales_Model_Quote $quote)
    {
        $items = $quote->getAllItems();
        $total = count($items);
        $storeId = $quote->getStoreId();
        $this->_log->trace("New quote; total items: $total; store: $storeId;");
        foreach ($items as $item) {
            /** @var $item Mage_Sales_Model_Quote_Item */
            $itemId = $item->getId();
            $sku = $item->getSku();
            $qty = $item->getQtyOrdered();
            $price = $item->getBasePrice();
            $tax = $item->getBaseTaxAmount();
            $discount = $item->getBaseDiscountAmount();
            $rowTotal = $item->getBaseRowTotal();
            $pvUnit = $item->getData(Nmmlm_Core_Config::ATTR_COMMON_PV_UNIT);
            $pvTotal = $item->getData(Nmmlm_Core_Config::ATTR_COMMON_PV_TOTAL);
            $appliedRules = $item->getAppliedRuleIds();
            $this->_log->trace("\tItem data: ID: $itemId; SKU: $sku; Qty: $qty; Price: $price; Discount: $discount; Tax: $tax; Total: $rowTotal; PV Unit: $pvUnit; PV Total: $pvTotal; Sales Rules: $appliedRules;");
        }
    }

    protected function _calcRetailBonusFee($amount)
    {
        $result = 0;
        if ($amount > 0) {
            $fixed = $this->_helper->cfgRetailBonusFeeFixed();
            $percent = $this->_helper->cfgRetailBonusFeePercent();
            $min = $this->_helper->cfgRetailBonusFeeMin();
            $max = $this->_helper->cfgRetailBonusFeeMax();
            $result = $fixed + $amount * $percent;
            $result = ($result < $min) ? $min : $result;
            $result = ($result > $max) ? $max : $result;
            $result = $this->_helper->formatAmount($result);
            $this->_log->trace("Retail bonus fee for amount $amount is $result ($min < [$fixed + $amount * $percent] < $max).");
        } else {
            $this->_log->trace("Retail bonus fee for amount $amount is not calculated (=0.00).");
        }
        return $result;
    }

}