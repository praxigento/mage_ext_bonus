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
 * @method int getCustomerId()
 * @method null setCustomerId(int $val)
 * @method string getDateChanged()
 * @method null setDateChanged(string $val)
 * @method int getTypeId()
 * @method null setTypeId(string $val)
 * @method decimal getValue()
 * @method null setValue(decimal $val)
 *
 */
class Praxigento_Bonus_Model_Own_Log_Bonus extends Mage_Core_Model_Abstract
{
    const ATTR_CUSTOMER_ID = 'customer_id';
    const ATTR_DATE_CHANGED = 'date_changed';
    const ATTR_ID = 'id';
    const ATTR_TYPE_ID = 'type_id';
    const ATTR_VALUE = 'value';

    protected function _construct()
    {
        $this->_init(Config::CFG_MODEL . '/' . Config::ENTITY_LOG_BONUS);
    }

}