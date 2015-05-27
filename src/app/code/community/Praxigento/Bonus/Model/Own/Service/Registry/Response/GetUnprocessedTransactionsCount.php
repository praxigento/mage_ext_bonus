<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Model_Own_Service_Registry_Response_GetUnprocessedTransactionsCount
    extends Praxigento_Bonus_Model_Own_Service_Base_Response
{
    private $_count = null;

    /**
     * @return null
     */
    public function getCount()
    {
        return $this->_count;
    }

    /**
     * @param null $val
     */
    public function setCount($val)
    {
        $this->_count = $val;
    }


    public function isSucceed()
    {
        $result = !is_null($this->_count);
        return $result;
    }
}