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
 * @method int getCustomerId()
 * @method null setCustomerId(int $val)
 * @method int getTypeId()
 * @method null setTypeId(int $val)
 * @method decimal getValue()
 * @method null setValue(decimal $val)
 */
class Praxigento_Bonus_Model_Own_Snap_Bonus extends Mage_Core_Model_Abstract
{
    const ATTR_CUSTOMER_ID = 'customer_id';
    const ATTR_ID = 'id';
    const ATTR_TYPE_ID = 'type_id';
    const ATTR_VALUE = 'value';

    protected function _construct()
    {
        $this->_init(Config::CFG_MODEL . '/' . Config::ENTITY_SNAP_BONUS);
    }

}