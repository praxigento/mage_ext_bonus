<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Service_Operations_Request_CreateOperationPvWriteOff
    extends Praxigento_Bonus_Service_Base_Request
{
    /** @var  decimal */
    private $_value;
    /** @var  string */
    private $_dateApplied;
    /** @var  int */
    private $_customerAccountId;

    /**
     * @return int
     */
    public function getCustomerAccountId()
    {
        return $this->_customerAccountId;
    }

    /**
     * @param int $val
     */
    public function setCustomerAccountId($val)
    {
        $this->_customerAccountId = $val;
    }

    /**
     * @return string
     */
    public function getPeriodCode()
    {
        return $this->_periodCode;
    }

    /**
     * @param string $val
     */
    public function setPeriodCode($val)
    {
        $this->_periodCode = $val;
    }

    /** @var  string 20150601 | 201506 | 2015 */
    private $_periodCode;

    /**
     * @return string
     */
    public function getDateApplied()
    {
        return $this->_dateApplied;
    }

    /**
     * @param string $val
     */
    public function setDateApplied($val)
    {
        $this->_dateApplied = $val;
    }

    /**
     * @return decimal
     */
    public function getValue()
    {
        return $this->_value;
    }

    /**
     * @param decimal $val
     */
    public function setValue($val)
    {
        $this->_value = $val;
    }
}