<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;

/**
 * Sale order related activity.
 *
 * User: Alex Gusev <alex@flancer64.com>
 *
 * @method int getCalcTypeId()
 * @method null setCalcTypeId(int $val)
 * @method string getDateChanged()
 * @method null setDateChanged(string $val)
 * @method int getOrderId()
 * @method null setOrderId(int $val)
 * @method decimal getValue()
 * @method null setValue(decimal $val)
 *
 */
class Praxigento_Bonus_Model_Own_Log_Order extends Mage_Core_Model_Abstract
{
    const ATTR_CALC_TYPE_ID = 'calc_type_id';
    const ATTR_DATE_CHANGED = 'date_changed';
    const ATTR_ID = 'id';
    const ATTR_ORDER_ID = 'order_id';
    const ATTR_VALUE = 'value';

    protected function _construct()
    {
        $this->_init(Config::CFG_MODEL . '/' . Config::ENTITY_LOG_ORDER);
    }

}