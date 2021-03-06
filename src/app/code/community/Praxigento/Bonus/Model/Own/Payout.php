<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;

/**
 * User: Alex Gusev <alex@flancer64.com>
 *
 * @method decimal getAmount()
 * @method null setAmount(decimal $val)
 * @method string getCurrency()
 * @method null setCurrency(string $val)
 * @method int getCustomerId()
 * @method null setCustomerId(int $val)
 * @method string getDateCreated()
 * @method null setDateCreated(string $val)
 * @method string getDatePaid()
 * @method null setDatePaid(string $val)
 * @method string getDescription()
 * @method null setDescription(string $val)
 * @method string getReference()
 * @method null setReference(string $val)
 */
class Praxigento_Bonus_Model_Own_Payout extends Mage_Core_Model_Abstract {
    const ATTR_AMOUNT = 'amount';
    const ATTR_CURR = 'currency';
    const ATTR_CUSTOMER_ID = 'customer_id';
    const ATTR_DATE_CREATED = 'date_created';
    const ATTR_DATE_PAID = 'date_paid';
    const ATTR_DESC = 'description';
    const ATTR_ID = 'id';
    const ATTR_REFERENCE = 'reference';

    protected function _construct() {
        $this->_init(Config::CFG_MODEL . '/' . Config::CFG_ENTITY_PAYOUT);
    }
}