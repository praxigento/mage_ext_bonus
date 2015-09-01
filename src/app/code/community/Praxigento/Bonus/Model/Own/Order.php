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
 * @method decimal getFee()
 * @method null setFee(decimal $val)
 * @method decimal getFeeFixed()
 * @method null setFeeFixed(decimal $val)
 * @method decimal getFeeMax()
 * @method null setFeeMax(decimal $val)
 * @method decimal getFeeMin()
 * @method null setFeeMin(decimal $val)
 * @method decimal getFeePercent()
 * @method null setFeePercent(decimal $val)
 * @method int getOrderId()
 * @method null setOrderId(int $val)
 * @method int getTransactId()
 * @method null setTransactId(int $val)
 * @method int getUplineId()
 * @method null setUplineId(int $val)
 */
class Praxigento_Bonus_Model_Own_Order extends Mage_Core_Model_Abstract {
    const ATTR_AMOUNT = 'amount';
    const ATTR_CURR = 'currency';
    const ATTR_FEE = 'fee';
    const ATTR_FEE_FIXED = 'fee_fixed';
    const ATTR_FEE_MAX = 'fee_max';
    const ATTR_FEE_MIN = 'fee_min';
    const ATTR_FEE_PERCENT = 'fee_percent';
    const ATTR_ID = 'id';
    const ATTR_ORDER_ID = 'order_id';
    const ATTR_TRANSACT_ID = 'transact_id';
    const ATTR_UPLINE_ID = 'upline_id';

    protected function _construct() {
        $this->_init(Config::CFG_MODEL . '/' . Config::CFG_ENTITY_ORDER);
    }

}