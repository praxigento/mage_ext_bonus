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
 * @method bool getIsCharged()
 * @method null setIsCharged(bool $val)
 * @method int getOrderId()
 * @method null setOrderId(int $val)
 * @method int getUplineId()
 * @method null setUplineId(int $val)
 */
class Praxigento_Bonus_Model_Own_Order extends Mage_Core_Model_Abstract
{
    const ATTR_AMOUNT = 'amount';
    const ATTR_CURR = 'currency';
    const ATTR_ID = 'id';
    const ATTR_IS_CHARGED = 'is_charged';
    const ATTR_ORDER_ID = 'order_id';
    const ATTR_UPLINE_ID = 'upline_id';
}