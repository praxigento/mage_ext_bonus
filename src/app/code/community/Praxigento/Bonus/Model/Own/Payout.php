<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */

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
 * @method string getReference()
 * @method null setReference(string $val)
 */
class Praxigento_Bonus_Model_Own_Payout extends Mage_Core_Model_Abstract
{
    const ATTR_AMOUNT = 'amount';
    const ATTR_CURR = 'currency';
    const ATTR_CUSTOMER_ID = 'customer_id';
    const ATTR_DATE_CREATED = 'date_created';
    const ATTR_ID = 'id';
    const ATTR_REFERENCE = 'reference';
}