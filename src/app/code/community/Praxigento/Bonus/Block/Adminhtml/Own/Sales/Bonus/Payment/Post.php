<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Block_Adminhtml_Own_Sales_Bonus_Payment_Post
    extends Praxigento_Bonus_Block_Adminhtml_Own_Sales_Bonus_Base {
    private $_createdCount;

    /**
     * @return mixed
     */
    public function getCreatedCount() {
        return $this->_createdCount;
    }

    /**
     * @param mixed $val
     */
    public function setCreatedCount($val) {
        $this->_createdCount = $val;
    }
}