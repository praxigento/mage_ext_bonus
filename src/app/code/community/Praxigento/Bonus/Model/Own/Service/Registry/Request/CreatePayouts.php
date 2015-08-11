<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Model_Own_Service_Registry_Request_CreatePayouts
    extends Praxigento_Bonus_Service_Base_Request
{
    /** @var  string */
    private $_description;

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->_description;
    }

    /**
     * @param string $val
     */
    public function setDescription($val)
    {
        $this->_description = $val;
    }
}