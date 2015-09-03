<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Service_Operations_Response_GetOperationsForPvWriteOff
    extends Praxigento_Bonus_Service_Base_Response {

    const TRN_CREDIT_ACC_ID = 'trn_credit_acc_id';
    const TRN_DATA_APPLIED = 'trn_date_applied';
    const TRN_DEBIT_ACC_ID = 'trn_debit_acc_id';
    const TRN_ID = 'trn_id';
    const TRN_VALUE = 'trn_value';
    /** @var  Varien_Data_Collection */
    private $_collection;

    /**
     * @return Varien_Data_Collection
     */
    public function getCollection() {
        return $this->_collection;
    }

    /**
     * @param Varien_Data_Collection $collection
     */
    public function setCollection(Varien_Data_Collection $collection) {
        $this->_collection = $collection;
    }

    public function isSucceed() {
        $result = ($this->getErrorCode() == self::ERR_NO_ERROR);
        return $result;
    }

}