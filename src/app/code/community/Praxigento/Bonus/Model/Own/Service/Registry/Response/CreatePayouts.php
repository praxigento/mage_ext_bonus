<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Model_Own_Service_Registry_Response_CreatePayouts
    extends Praxigento_Bonus_Service_Base_Response
{
    private $_payoutIds = array();

    /**
     * @return array
     */
    public function getPayoutIds()
    {
        return $this->_payoutIds;
    }

    /**
     * @param array $val
     */
    public function setPayoutIds($val)
    {
        $this->_payoutIds = $val;
    }

    public function isSucceed()
    {
        $result = is_array($this->_payoutIds) && (count($this->_payoutIds));
        return $result;
    }
}