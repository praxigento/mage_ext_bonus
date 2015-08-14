<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;

/**
 * Calculations log.
 *
 * User: Alex Gusev <alex@flancer64.com>
 *
 * @method string getDatePerformed()
 * @method null setDatePerformed(string $val)
 * @method int getPeriodId()
 * @method null setPeriodId(int $val)
 * @method string getState()
 * @method null setState(string $val)
 *
 */
class Praxigento_Bonus_Model_Own_Log_Calc extends Mage_Core_Model_Abstract
{
    const ATTR_DATE_PERFORMED = 'date_performed';
    const ATTR_ID = 'id';
    const ATTR_PERIOD_ID = 'period_id';
    const ATTR_STATE = 'state';

    protected function _construct()
    {
        $this->_init(Config::CFG_MODEL . '/' . Config::ENTITY_LOG_BONUS);
    }

}