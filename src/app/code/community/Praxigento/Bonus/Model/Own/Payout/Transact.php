<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;

/**
 * User: Alex Gusev <alex@flancer64.com>
 *
 * @method int getPayoutId()
 * @method null setPayoutId(int $val)
 * @method int getTransactId()
 * @method null setTransactId(int $val)
 */
class Praxigento_Bonus_Model_Own_Payout_Transact extends Mage_Core_Model_Abstract {
    const ATTR_PAYOUT_ID = 'payout_id';
    const ATTR_TRANSACT_ID = 'transact_id';

    protected function _construct() {
        $this->_init(Config::CFG_MODEL . '/' . Config::CFG_ENTITY_PAYOUT_TRANSACT);
    }
}