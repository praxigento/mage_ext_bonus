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
abstract class Praxigento_Bonus_Service_Base_Call {
    /** @var Praxigento_Bonus_Helper_Data */
    protected $_helper;
    /** @var Praxigento_Bonus_Helper_Account */
    protected $_helperAccount;
    /** @var  Nmmlm_Core_Helper_Data */
    protected $_helperCore;
    /** @var Praxigento_Bonus_Helper_Period */
    protected $_helperPeriod;
    /** @var Praxigento_Bonus_Helper_Type */
    protected $_helperType;
    /** @var  Praxigento_Bonus_Logger */
    protected $_log;

    function __construct() {
        $this->_log = Config::get()->logger(__CLASS__);
        /* helpers */
        $this->_helper        = Config::get()->helper();
        $this->_helperAccount = Config::get()->helperAccount();
        $this->_helperPeriod  = Config::get()->helperPeriod();
        $this->_helperType    = Config::get()->helperType();
        /* Nmmlm_Core helper */
        $this->_helperCore = Config::get()->helperCore();
    }
}