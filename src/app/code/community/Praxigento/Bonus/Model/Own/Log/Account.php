<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;

/**
 * Account related activity.
 *
 * User: Alex Gusev <alex@flancer64.com>
 *
 * @method string getCurrency()
 * @method null setCurrency(string $val)
 * @method int getCustomerId()
 * @method null setCustomerId(int $val)
 * @method string getDateChanged()
 * @method null setDateChanged(string $val)
 * @method decimal getValue()
 * @method null setValue(decimal $val)
 *
 */
class Praxigento_Bonus_Model_Own_Log_Account extends Mage_Core_Model_Abstract {
    const ATTR_CURR = 'currency';
    const ATTR_CUSTOMER_ID = 'customer_id';
    const ATTR_DATE_CHANGED = 'date_changed';
    const ATTR_ID = 'id';
    const ATTR_VALUE = 'value';

    protected function _construct() {
        $this->_init(Config::ENTITY_LOG_ACCOUNT);
    }

}