<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;

/**
 * Base class for all services calls (operations aggregations).
 *
 * User: Alex Gusev <alex@flancer64.com>
 */
abstract class Praxigento_Bonus_Service_Base_Call
{
    /** @var  Praxigento_Bonus_Logger */
    protected $_log;
    /** @var Praxigento_Bonus_Helper_Data */
    protected $_helper;

    function __construct()
    {
        $this->_log = Praxigento_Bonus_Logger::getLogger(__CLASS__);
        $this->_helper = Mage::helper(Config::CFG_HELPER);
    }
}