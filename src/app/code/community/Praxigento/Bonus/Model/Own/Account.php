<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;

/**
 * Customer accounts to register asset transition.
 *
 * User: Alex Gusev <alex@flancer64.com>
 *
 * @method int getAssetId()
 * @method null setAssetId(int $val)
 * @method int getCustomerId()
 * @method null setCustomerId(int $val)
 */
class Praxigento_Bonus_Model_Own_Account extends Mage_Core_Model_Abstract
{
    const ATTR_ASSET_ID = 'asset_id';
    const ATTR_CUSTOMER_ID = 'customer_id';
    const ATTR_ID = 'id';

    protected function _construct()
    {
        $this->_init(Config::CFG_MODEL . '/' . Config::ENTITY_ACCOUNT);
    }
}