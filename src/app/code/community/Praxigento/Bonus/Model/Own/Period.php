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
 * @method int getCalcTypeId()
 * @method null setCalcTypeId(int $val)
 * @method string getType()
 * @method null setType(string $val)
 * @method string getValue()
 * @method null setValue(string $val)
 */
class Praxigento_Bonus_Model_Own_Period
    extends Mage_Core_Model_Abstract {
    const ATTR_CALC_TYPE_ID = 'calc_type_id';
    const ATTR_ID = 'id';
    const ATTR_TYPE = 'type';
    const ATTR_VALUE = 'value';

    protected function _construct() {
        $this->_init(Config::ENTITY_PERIOD);
    }
}