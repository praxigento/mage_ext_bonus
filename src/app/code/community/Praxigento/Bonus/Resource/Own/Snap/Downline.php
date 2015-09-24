<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;
use Praxigento_Bonus_Model_Own_Snap_Downline as Model;

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Resource_Own_Snap_Downline extends Mage_Core_Model_Mysql4_Abstract {
    /**
     * Resource initialization
     */
    protected function _construct() {
        $this->_init(Config::ENTITY_SNAP_DOWNLINE, Model::ATTR_CUSTOMER_ID);
        $this->_isPkAutoIncrement = false;
        $this->_idFieldName = Model::ATTR_CUSTOMER_ID;
    }

}