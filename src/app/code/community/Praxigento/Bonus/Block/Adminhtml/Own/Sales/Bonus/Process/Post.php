<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Block_Adminhtml_Own_Sales_Bonus_Process_Post extends Praxigento_Bonus_Block_Adminhtml_Own_Base
{
    private $_processed;

    /**
     * @return mixed
     */
    public function getProcessed()
    {
        return $this->_processed;
    }

    /**
     * @param mixed $val
     */
    public function setProcessed($val)
    {
        $this->_processed = $val;
    }
}