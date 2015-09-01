<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Model_Own_Service_Registry_Request_SaveRetailBonus
    extends Praxigento_Bonus_Service_Base_Request {
    /** @var  Mage_Sales_Model_Order */
    private $order;

    /**
     * @return Mage_Sales_Model_Order
     */
    public function getOrder() {
        return $this->order;
    }

    /**
     * @param Mage_Sales_Model_Order $val
     */
    public function setOrder(Mage_Sales_Model_Order $val) {
        $this->order = $val;
    }
}