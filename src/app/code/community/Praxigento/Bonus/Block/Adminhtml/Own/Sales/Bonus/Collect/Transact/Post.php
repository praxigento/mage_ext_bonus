<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Block_Adminhtml_Own_Sales_Bonus_Collect_Transact_Post
    extends Praxigento_Bonus_Block_Adminhtml_Own_Sales_Bonus_Collect_Base
{
    private $_collectedCount;

    /**
     * @return mixed
     */
    public function getCollectedCount()
    {
        return $this->_collectedCount;
    }

    /**
     * @param mixed $val
     */
    public function setCollectedCount($val)
    {
        $this->_collectedCount = $val;
    }
}