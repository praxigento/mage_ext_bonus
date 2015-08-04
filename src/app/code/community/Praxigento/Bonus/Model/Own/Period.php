<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;

/**
 * Bonus calculation periods.
 *
 * User: Alex Gusev <alex@flancer64.com>
 *
 * @method int getBonusId()
 * @method null setBonusId(int $val)
 * @method string getState()
 * @method null setState(string $val)
 * @method string getType()
 * @method null setType(string $val)
 * @method string getValue()
 * @method null setValue(string $val)
 */
class Praxigento_Bonus_Model_Own_Period
    extends Mage_Core_Model_Abstract
{
    const ATTR_BONUS_ID = 'bonus_id';
    const ATTR_ID = 'id';
    const ATTR_STATE = 'state';
    const ATTR_TYPE = 'type';
    const ATTR_VALUE = 'value';

    protected function _construct()
    {
        $this->_init(Config::CFG_MODEL . '/' . Config::ENTITY_PERIOD);
    }
}