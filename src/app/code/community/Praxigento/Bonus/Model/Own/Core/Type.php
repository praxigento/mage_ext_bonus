<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;

/**
 * Bonus types codifier.
 *
 * User: Alex Gusev <alex@flancer64.com>
 *
 * @method string getCode()
 * @method null setCode(string $val)
 * @method string getNote()
 * @method null setNote(string $val)
 */
class Praxigento_Bonus_Model_Own_Core_Type extends Mage_Core_Model_Abstract
{
    const ATTR_CODE = 'code';
    const ATTR_ID = 'id';
    const ATTR_NOTE = 'note';

    protected function _construct()
    {
        $this->_init(Config::CFG_MODEL . '/' . Config::ENTITY_CORE_TYPE);
    }

}