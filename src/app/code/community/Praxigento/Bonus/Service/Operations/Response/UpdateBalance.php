<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Service_Operations_Response_UpdateBalance
    extends Praxigento_Bonus_Service_Base_Response {
    /**
     * @var Praxigento_Bonus_Model_Own_Balance
     */
    private $_balance;

    /**
     * @return Praxigento_Bonus_Model_Own_Balance
     */
    public function getBalance() {
        return $this->_balance;
    }

    /**
     * @param Praxigento_Bonus_Model_Own_Balance $val
     */
    public function setBalance(Praxigento_Bonus_Model_Own_Balance $val) {
        $this->_balance = $val;
    }

    public function isSucceed() {
        $result = ($this->getErrorCode() == self::ERR_NO_ERROR);
        return $result;
    }

}