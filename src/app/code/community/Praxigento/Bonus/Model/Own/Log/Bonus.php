<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;

/**
 * Bonus related activity.
 *
 * User: Alex Gusev <alex@flancer64.com>
 *
 */
class Praxigento_Bonus_Model_Own_Log_Bonus extends Mage_Core_Model_Abstract
{
    const ATTR_ID = 'id';

    protected function _construct()
    {
        $this->_init(Config::CFG_MODEL . '/' . Config::ENTITY_LOG_BONUS);
    }

}