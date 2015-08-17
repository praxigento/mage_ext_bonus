<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;
use Praxigento_Bonus_Model_Own_Type_Calc as Model;

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Resource_Own_Type_Calc
    extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Resource initialization
     */
    protected function _construct()
    {
        $this->_init(Config::CFG_MODEL . '/' . Config::ENTITY_TYPE_CALC, Model::ATTR_ID);
    }

}