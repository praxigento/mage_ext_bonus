<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Model_Own_Service_Replica_Request_CreateQuoteFromOrder as CreateQuoteFromOrderRequest;
use Praxigento_Bonus_Model_Own_Service_Replica_Response_CreateQuoteFromOrder as CreateQuoteFromOrderResponse;

/**
 * Service to create quote replicas (copies) from other orders or quotes.
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Model_Own_Service_Replica_Call extends Praxigento_Bonus_Model_Own_Service_Base_Call
{
    /**
     * Create quote for a given customer from a sample order.
     *
     * @param Praxigento_Bonus_Model_Own_Service_Replica_Request_CreateQuoteFromOrder $req
     * @return Praxigento_Bonus_Model_Own_Service_Replica_Response_CreateQuoteFromOrder
     */
    public function createQuoteFromOrder(CreateQuoteFromOrderRequest $req)
    {
        $result = Mage::getModel('prxgt_bonus_model/own_service_replica_response_createQuoteFromOrder');
        /* extract data from request and load data models */
        $customer = $this->_initCustomer($req->getCustomerId(), $req->getCustomer());
        $customerGroupId = $customer->getGroupId();
        $order = $this->_initOrder($req->getOrderId(), $req->getOrder());
        $storeId = $order->getStoreId();
        /**
         * Populate quote with order's data.
         */
        $quote = Mage::getModel('sales/quote');
        $quote->setCustomer($customer);
        /**
         *  Transfer order data to quote.
         */
        $this->_initRuleData($customerGroupId, $storeId);
        $this->_initQuoteItems($quote, $order, $storeId, $customerGroupId);
        $this->_initBillingAddressFromOrder($quote, $order);
        $this->_initShippingAddressFromOrder($quote, $order);
        /**
         * Re-collect totals and return quote.
         */
        $quote->collectTotals();
        $result->setQuote($quote);
        return $result;
    }

    /**
     * @param int $id
     * @param Mage_Customer_Model_Customer $model
     * @return Mage_Customer_Model_Customer|null
     */
    private function _initCustomer($id = null, $model = null)
    {
        $result = null;
        if (
            !is_null($model) &&
            ($model instanceof Mage_Customer_Model_Customer) &&
            ($model->getId())
        ) {
            /* there is model data in the request */
            $result = $model;
        } else if (!is_null($id)) {
            /* there is customer id in the request, load model data */
            $loaded = Mage::getModel('customer/customer')->load($id);
            if ($loaded->getId()) {
                $result = $loaded;
            }
        } else {
            $this->_log->error('Cannot initiate customer data');
        }
        return $result;
    }

    /**
     * @param int $id
     * @param Mage_Sales_Model_Order $model
     * @return Mage_Sales_Model_Order|null
     */
    private function _initOrder($id = null, $model = null)
    {
        $result = null;
        if (
            !is_null($model) &&
            ($model instanceof Mage_Sales_Model_Order) &&
            ($model->getId())
        ) {
            /* there is model data in the request */
            $result = $model;
        } else if (!is_null($id)) {
            /* there is customer id in the request, load model data */
            $loaded = Mage::getModel('sales/order')->load($id);
            if ($loaded->getId()) {
                $result = $loaded;
            }
        } else {
            $this->_log->error('Cannot initiate order data');
        }
        return $result;
    }

    /**
     * Initialize data for price rules (see \Mage_Adminhtml_Model_Sales_Order_Create::_initRuleData)
     */
    protected function _initRuleData($customerGroupId, $storeId)
    {
        $registryKey = 'rule_data';
        if (is_null(Mage::registry($registryKey))) {
            $store = Mage::getModel('core/store')->load($storeId);
            $ruleDataArray = array(
                'store_id' => $store->getId(),
                'website_id' => $store->getWebsiteId(),
                'customer_group_id' => $customerGroupId,
            );
            $ruleData = new Varien_Object($ruleDataArray);
            Mage::register($registryKey, $ruleData);
        }
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @param Mage_Sales_Model_Order $order
     * @param $storeId
     * @param $customerGid
     */
    private function _initQuoteItems(
        Mage_Sales_Model_Quote $quote,
        Mage_Sales_Model_Order $order,
        $storeId,
        $customerGid
    )
    {
        /* transfer order items */
        $items = $order->getItemsCollection(
            array_keys(Mage::getConfig()->getNode('adminhtml/sales/order/create/available_product_types')->asArray()),
            true
        );
        foreach ($items as $orderItem) {
            /* @var $orderItem Mage_Sales_Model_Order_Item */
            if (!$orderItem->getParentItem()) {
                $qty = $orderItem->getQtyOrdered();
                if ($qty > 0) {
                    $item = $this->_initQuoteItemFromOrderItem($quote, $orderItem, $storeId, $qty, $customerGid);
                    if (is_string($item)) {
                        Mage::throwException($item);
                    }
                }
            }
        }

    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @param Mage_Sales_Model_Order_Item $orderItem
     * @param $storeId
     * @param null $qty
     * @param $customerGid
     * @return $this|null
     */
    private function _initQuoteItemFromOrderItem(
        Mage_Sales_Model_Quote $quote,
        Mage_Sales_Model_Order_Item $orderItem,
        $storeId,
        $qty = null,
        $customerGid)
    {
        if (!$orderItem->getId()) {
            return $this;
        }

        /** INTR-652 */
        /** @var  $product Mage_Catalog_Model_Product */
        $product = Mage::getModel('catalog/product');
        $product->setStoreId($storeId);
        $sku = $orderItem->getSku();
        $productId = $product->getIdBySku($sku);
        $product->load($productId);
        /** INTR-706: customer group is used in \Amasty_Table_Model_Carrier_Table::collectRates */
        $product->setCustomerGroupId($customerGid);

        if ($product->getId()) {
            $product->setSkipCheckRequiredOption(true);
            $buyRequest = $orderItem->getBuyRequest();
            if (is_numeric($qty)) {
                $buyRequest->setQty($qty);
            }
            $item = $quote->addProduct($product, $buyRequest);

            if (is_string($item)) {
                return $item;
            }

            if ($additionalOptions = $orderItem->getProductOptionByCode('additional_options')) {
                $item->addOption(new Varien_Object(
                    array(
                        'product' => $item->getProduct(),
                        'code' => 'additional_options',
                        'value' => serialize($additionalOptions)
                    )
                ));
            }

            Mage::dispatchEvent('sales_convert_order_item_to_quote_item', array(
                'order_item' => $orderItem,
                'quote_item' => $item
            ));
            return $item;
        }

        return null;
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @param Mage_Sales_Model_Order $order
     */
    protected function _initBillingAddressFromOrder(Mage_Sales_Model_Quote $quote, Mage_Sales_Model_Order $order)
    {
        $quote->getBillingAddress()->setCustomerAddressId($order->getBillingAddressId());
        Mage::helper('core')->copyFieldset(
            'sales_copy_order_billing_address',
            'to_order',
            $order->getBillingAddress(),
            $quote->getBillingAddress()
        );
        /** INTR-800, INTR-948 */
//        $quote->getBillingAddress()->save();
        //$quote->getBillingAddress()->setData('address_id', $order->getBillingAddressId());
    }

    protected function _initShippingAddressFromOrder(Mage_Sales_Model_Quote $quote, Mage_Sales_Model_Order $order)
    {
        $quote->getShippingAddress()->setCustomerAddressId('');
        Mage::helper('core')->copyFieldset(
            'sales_copy_order_shipping_address',
            'to_order',
            $order->getShippingAddress(),
            $quote->getShippingAddress()
        );
        /** INTR-800 */
//        $quote->getShippingAddress()->save();
        // $this->_quote->getShippingAddress()->setData('address_id', $order->getShippingAddressId());
    }
}