<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Model_Own_Service_Registry_Response_CreatePayments
    extends Praxigento_Bonus_Service_Base_Response {
    private $_paymentsRefs = array();

    public function isSucceed() {
        $result = is_array($this->_paymentsRefs) && (count($this->_paymentsRefs));
        return $result;
    }

    /**
     * @return array
     */
    public function getPaymentsRefs() {
        return $this->_paymentsRefs;
    }

    /**
     * @param array $val
     */
    public function setPaymentsRefs($val) {
        $this->_paymentsRefs = $val;
    }
}