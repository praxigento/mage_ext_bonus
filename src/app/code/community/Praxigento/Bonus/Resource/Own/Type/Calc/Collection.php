<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Resource_Own_Type_Calc_Collection
    extends Mage_Core_Model_Mysql4_Collection_Abstract {
    /**
     * Resource initialization
     */
    protected function _construct() {
        $this->_init(Config::ENTITY_TYPE_CALC);
    }
}