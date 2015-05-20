<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Model_Own_Service_Registry_Response_SaveRetailBonus
    extends Praxigento_Bonus_Model_Own_Service_Base_Response
{

    /** @var  Praxigento_Bonus_Model_Own_Order */
    private $bonusOrder;

    /**
     * @return Praxigento_Bonus_Model_Own_Order
     */
    public function getBonusOrder()
    {
        return $this->bonusOrder;
    }

    /**
     * @param Praxigento_Bonus_Model_Own_Order $val
     */
    public function setBonusOrder(Praxigento_Bonus_Model_Own_Order $val)
    {
        $this->bonusOrder = $val;
    }

    public function isSucceed()
    {
        $result = (isset($this->bonusOrder) && $this->bonusOrder->getId());
        return $result;
    }
}