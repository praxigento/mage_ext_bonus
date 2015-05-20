<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Model_Own_Service_Replica_Request_CreateQuoteFromOrder
    extends Praxigento_Bonus_Model_Own_Service_Base_Request
{
    /** @var  int */
    private $customerId;
    /** @var  Mage_Customer_Model_Customer */
    private $customer;
    /** @var  int */
    private $orderId;
    /** @var  Mage_Sales_Model_Order */
    private $order;

    /**
     * @return int
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * @param int $val
     */
    public function setCustomerId($val)
    {
        $this->customerId = $val;
    }

    /**
     * @return Mage_Customer_Model_Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param Mage_Customer_Model_Customer $val
     */
    public function setCustomer(Mage_Customer_Model_Customer $val)
    {
        $this->customer = $val;
    }

    /**
     * @return int
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @param int $val
     */
    public function setOrderId($val)
    {
        $this->orderId = $val;
    }

    /**
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param Mage_Sales_Model_Order $val
     */
    public function setOrder(Mage_Sales_Model_Order $val)
    {
        $this->order = $val;
    }

}