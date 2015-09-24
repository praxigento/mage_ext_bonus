<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;

/**
 * Operations with assets (one ore more transactions in set).
 *
 * User: Alex Gusev <alex@flancer64.com>
 *
 * @method string getDatePerformed()
 * @method null setDatePerformed(string $val)
 * @method int getTypeId()
 * @method null setTypeId(int $val)
 */
class Praxigento_Bonus_Model_Own_Operation
    extends Mage_Core_Model_Abstract {
    const ATTR_DATE_PERFORMED = 'date_performed';
    const ATTR_ID = 'id';
    const ATTR_TYPE_ID = 'type_id';

    protected function _construct() {
        $this->_init(Config::ENTITY_OPERATION);
    }
}