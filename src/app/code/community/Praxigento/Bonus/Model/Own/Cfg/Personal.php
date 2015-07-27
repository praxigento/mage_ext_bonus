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
 * @method decimal getFrom()
 * @method null setFrom(decimal $val)
 * @method decimal getPercent()
 * @method null setPercent(decimal $val)
 * @method decimal getTo()
 * @method null setTo(decimal $val)
 */
class Praxigento_Bonus_Model_Own_Cfg_Personal extends Mage_Core_Model_Abstract
{
    /** Low level of PV per period for applied percent (included). */
    const ATTR_FROM = 'from';
    const ATTR_ID = 'id';
    /** Percent applied to PV collected per period to compute bonus value. */
    const ATTR_PERCENT = 'persent';
    /** High level of PV per period for applied percent (excluded). */
    const ATTR_TO = 'to';

    protected function _construct()
    {
        $this->_init(Config::CFG_MODEL . '/' . Config::ENTITY_CFG_PERSONAL);
    }

}