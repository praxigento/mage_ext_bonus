<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;

/**
 * Downline tree snapshot for current state.
 *
 * User: Alex Gusev <alex@flancer64.com>
 *
 * @method int getCustomerId()
 * @method null setCustomerId(int $val)
 * @method int getDepth()
 * @method null setDepth(int $val)
 * @method int getNdx()
 * @method null setNdx(int $val)
 * @method int getParentId()
 * @method null setParentId(int $val)
 * @method string getPath()
 * @method null setPath(string $val)
 * @method string getPeriod()
 * @method null setPeriod(string $val)
 */
class Praxigento_Bonus_Model_Own_Snap_Downline extends Mage_Core_Model_Abstract {
    const ATTR_CUSTOMER_ID = 'customer_id';
    const ATTR_DEPTH = 'depth';
    const ATTR_ID = 'id';
    const ATTR_NDX = 'ndx';
    const ATTR_PARENT_ID = 'parent_id';
    const ATTR_PATH = 'path';
    const ATTR_PERIOD = 'period';

    protected function _construct() {
        $this->_init(Config::ENTITY_SNAP_DOWNLINE);
    }

}