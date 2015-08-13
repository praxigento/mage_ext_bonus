<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;

/**
 * Bonus snapshot for current state.
 *
 * User: Alex Gusev <alex@flancer64.com>
 *
 * @method int getCalcTypeTypeId()
 * @method null setCalcTypeTypeId(int $val)
 * @method int getCustomerId()
 * @method null setCustomerId(int $val)
 * @method string getPeriod()
 * @method null setPeriod(string $val)
 * @method decimal getValue()
 * @method null setValue(decimal $val)
 */
class Praxigento_Bonus_Model_Own_Snap_Bonus extends Mage_Core_Model_Abstract
{
    const ATTR_CALC_TYPE_ID = 'calc_type_id';
    const ATTR_CUSTOMER_ID = 'customer_id';
    const ATTR_ID = 'id';
    const ATTR_PERIOD = 'period';
    const ATTR_VALUE = 'value';

    protected function _construct()
    {
        $this->_init(Config::CFG_MODEL . '/' . Config::ENTITY_SNAP_BONUS);
    }

}