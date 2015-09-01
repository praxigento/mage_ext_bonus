<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;

/**
 * Asset accounts balances (current and history).
 *
 * User: Alex Gusev <alex@flancer64.com>
 *
 * @method int getAccountId()
 * @method null setAccountId(int $val)
 * @method string getPeriod()
 * @method null setPeriod(string $val)
 * @method decimal getValue()
 * @method null setValue(decimal $val)
 */
class Praxigento_Bonus_Model_Own_Balance extends Mage_Core_Model_Abstract {
    const ATTR_ACCOUNT_ID = 'account_id';
    const ATTR_ID = 'id';
    const ATTR_PERIOD = 'period';
    const ATTR_VALUE = 'value';

    protected function _construct() {
        $this->_init(Config::CFG_MODEL . '/' . Config::ENTITY_BALANCE);
    }
}