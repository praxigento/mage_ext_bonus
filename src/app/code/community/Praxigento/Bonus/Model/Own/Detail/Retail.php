<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;

/**
 * Retail bonus details.
 *
 * User: Alex Gusev <alex@flancer64.com>
 *
 * @method string getCurrency()
 * @method null setCurrency(string $val)
 * @method int getCustomerId()
 * @method null setCustomerId(int $val)
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
 */
class Praxigento_Bonus_Model_Own_Detail_Retail extends Mage_Core_Model_Abstract
{
    const ATTR_CURR = 'currency';
    const ATTR_CUSTOMER_ID = 'customer_id';
    const ATTR_FEE = 'fee';
    const ATTR_FEE_FIXED = 'fee_fixed';
    const ATTR_FEE_MAX = 'fee_max';
    const ATTR_FEE_MIN = 'fee_min';
    const ATTR_FEE_PERCENT = 'fee_percent';
    const ATTR_ID = 'id';
    const ATTR_ORDER_ID = 'order_id';

    protected function _construct()
    {
        $this->_init(Config::CFG_MODEL . '/' . Config::ENTITY_DETAIL_ORDER);
    }

}