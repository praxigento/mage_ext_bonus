<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;

/**
 * Types of the available assets.
 *
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Model_Own_Type_Asset
    extends Praxigento_Bonus_Model_Own_Type_Base {

    protected function _construct() {
        $this->_init(Config::ENTITY_TYPE_ASSET);
    }

}