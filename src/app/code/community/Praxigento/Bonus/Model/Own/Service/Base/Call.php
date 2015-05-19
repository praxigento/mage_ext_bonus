<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */

/**
 * Base class for all services calls (operations aggregations).
 *
 * User: Alex Gusev <alex@flancer64.com>
 */
abstract class Praxigento_Bonus_Model_Own_Service_Base_Call
{
    /** @var  Praxigento_Bonus_Logger */
    protected $_log;

    function __construct()
    {
        $this->_log = Praxigento_Bonus_Logger::getLogger(__CLASS__);
    }
}