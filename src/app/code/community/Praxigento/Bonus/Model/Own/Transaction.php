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
 * @method int getCreditAccId()
 * @method null setCreditAccId(int $val)
 * @method string getDateApplied()
 * @method null setDateApplied(string $val)
 * @method int getDebitAccId()
 * @method null setDebitAccId(int $val)
 * @method int getOperationId()
 * @method null setOperationId(int $val)
 * @method decimal getValue()
 * @method null setValue(decimal $val)
 */
class Praxigento_Bonus_Model_Own_Transaction
    extends Mage_Core_Model_Abstract
{
    const ATTR_CREDIT_ACC_ID = 'credit_acc_id';
    /* date when asset transaction should change balances (can be in the past) */
    const ATTR_DATE_APPLIED = 'date_applied';
    const ATTR_DEBIT_ACC_ID = 'debit_acc_id';
    const ATTR_ID = 'id';
    const ATTR_OPERATION_ID = 'operation_id';
    const ATTR_VALUE = 'value';

    protected function _construct()
    {
        $this->_init(Config::CFG_MODEL . '/' . Config::ENTITY_TRANSACTION);
    }
}