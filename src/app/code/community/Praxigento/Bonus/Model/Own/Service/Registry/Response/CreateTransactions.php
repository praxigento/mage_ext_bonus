<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Model_Own_Service_Registry_Response_CreateTransactions
    extends Praxigento_Bonus_Model_Own_Service_Base_Response
{
    private $_transactionIds = array();

    /**
     * @return array
     */
    public function getTransactionIds()
    {
        return $this->_transactionIds;
    }

    /**
     * @param array $val
     */
    public function setTransactionIds($val)
    {
        $this->_transactionIds = $val;
    }

    public function isSucceed()
    {
        $result = is_array($this->_transactionIds) && (count($this->_transactionIds));
        return $result;
    }
}