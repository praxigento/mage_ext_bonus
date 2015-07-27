<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;

/**
 * Configuration parameters for Personal Volume bonus.
 *
 * User: Alex Gusev <alex@flancer64.com>
 *
 * @method decimal getLevel()
 * @method null setLevel(decimal $val)
 * @method decimal getPercent()
 * @method null setPercent(decimal $val)
 */
class Praxigento_Bonus_Model_Own_Cfg_Personal extends Mage_Core_Model_Abstract
{
    const ATTR_ID = 'id';
    /** Low level of PV per period for applied percent (included). */
    const ATTR_LEVEL = 'level';
    /** Percent applied to PV collected per period to compute bonus value. */
    const ATTR_PERCENT = 'percent';

    protected function _construct()
    {
        $this->_init(Config::CFG_MODEL . '/' . Config::ENTITY_CFG_PERSONAL);
    }

}