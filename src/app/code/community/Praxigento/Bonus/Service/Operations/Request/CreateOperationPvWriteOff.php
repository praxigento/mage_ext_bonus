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
    /** @var  int */
    private $_debitAccountId;
    /** @var  int */
    private $_creditAccountId;
    /** @var  decimal */
    private $_value;
    /** @var  string */
    private $_dateApplied;

    /**
     * @return int
     */
    public function getCreditAccountId()
    {
        return $this->_creditAccountId;
    }

    /**
     * @param int $val
     */
    public function setCreditAccountId($val)
    {
        $this->_creditAccountId = $val;
    }

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
     * @return int
     */
    public function getDebitAccountId()
    {
        return $this->_debitAccountId;
    }

    /**
     * @param int $val
     */
    public function setDebitAccountId($val)
    {
        $this->_debitAccountId = $val;
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