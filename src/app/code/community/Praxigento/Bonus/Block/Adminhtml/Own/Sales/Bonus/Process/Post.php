<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Block_Adminhtml_Own_Sales_Bonus_Process_Post extends Praxigento_Bonus_Block_Adminhtml_Own_Base
{
    private $_processedCount;
    private $_failedOrders = array();

    /**
     * @return array
     */
    public function getFailedOrders()
    {
        return $this->_failedOrders;
    }

    /**
     * @param array $failedOrders
     */
    public function setFailedOrders($failedOrders)
    {
        $this->_failedOrders = $failedOrders;
    }

    /**
     * @return mixed
     */
    public function getProcessedCount()
    {
        return $this->_processedCount;
    }

    /**
     * @param mixed $val
     */
    public function setProcessedCount($val)
    {
        $this->_processedCount = $val;
    }

    public function isRetailBonusEnabled()
    {
        /** @var  $hlp Praxigento_Bonus_Helper_Data */
        $hlp = Mage::helper(Config::CFG_HELPER);
        $result = $hlp->cfgRetailBonusEnabled();
        return $result;
    }
}