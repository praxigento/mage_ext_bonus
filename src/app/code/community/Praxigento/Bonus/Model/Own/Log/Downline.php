<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;

/**
 * Downline tree related activity.
 *
 * User: Alex Gusev <alex@flancer64.com>
 *
 * @method int getCustomerId()
 * @method null setCustomerId(int $val)
 * @method string getDateChanged()
 * @method null setDateChanged(string $val)
 * @method int getParentId()
 * @method null setParentId(int $val)
 *
 */
class Praxigento_Bonus_Model_Own_Log_Downline extends Mage_Core_Model_Abstract {
    const ATTR_CUSTOMER_ID = 'customer_id';
    const ATTR_DATE_CHANGED = 'date_changed';
    const ATTR_ID = 'id';
    const ATTR_PARENT_ID = 'parent_id';

    protected function _construct() {
        $this->_init(Config::ENTITY_LOG_DOWNLINE);
    }

}