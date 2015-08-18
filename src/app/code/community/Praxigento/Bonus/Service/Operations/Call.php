<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;
use Praxigento_Bonus_Model_Own_Log_Calc as LogCalc;
use Praxigento_Bonus_Model_Own_Operation as Operation;
use Praxigento_Bonus_Model_Own_Period as Period;
use Praxigento_Bonus_Model_Own_Transaction as Transaction;
use Praxigento_Bonus_Service_Operations_Request_CreateOperationPvWriteOff as CreateOperationPvWriteOffRequest;
use Praxigento_Bonus_Service_Operations_Request_GetOperationsForPvWriteOff as GetOperationsForPvWriteOffRequest;
use Praxigento_Bonus_Service_Operations_Response_CreateOperationPvWriteOff as CreateOperationPvWriteOffResponse;
use Praxigento_Bonus_Service_Operations_Response_GetOperationsForPvWriteOff as GetOperationsForPvWriteOffResponse;

/**
 *
 * Retrieve operations collection for various calculations.
 *
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Service_Operations_Call
    extends Praxigento_Bonus_Service_Base_Call
{
    /** @var Praxigento_Bonus_Helper_Type */
    private $_helperType;

    /**
     * Praxigento_Bonus_Service_Operations_Call constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->_helperType = Config::get()->helperType();
    }

    /**
     * @param Praxigento_Bonus_Service_Operations_Request_GetOperationsForPvWriteOff $req
     * @return Praxigento_Bonus_Service_Operations_Response_GetOperationsForPvWriteOff
     */
    public function getOperationsForPvWriteOff(GetOperationsForPvWriteOffRequest $req)
    {
        $result = Mage::getModel('prxgt_bonus_service/operations_response_getOperationsForPvWriteOff');
        $calcId = $req->getCalcTypeId();
        $periodValue = $req->getPeriodValue();
        $periodCode = $req->getPeriodCode();
        /**
         *
         * SELECT
         *
         * FROM prxgt_bonus_operation pbo
         * LEFT OUTER JOIN prxgt_bonus_trnx pbt
         * ON pbo.id = pbt.operation_id
         * WHERE (
         * pbo.type_id = 1
         * OR pbo.type_id = 3
         * )
         * AND pbt.date_applied >= '2015-06-01 00:00:00'
         * AND pbt.date_applied <= '2015-06-01 23:59:59'
         */
        $collection = Config::get()->collectionOperation();
        /* filter by operations types */
        $fields = array();
        $values = array();
        $operIds = $this->_getTypesForPvWriteOff();
        foreach ($operIds as $one) {
            $fields[] = Operation::ATTR_TYPE_ID;
            $values[] = $one;
        }
        $collection->addFieldToFilter($fields, $values);
        $tableTrnx = $collection->getTable(Config::CFG_MODEL . '/' . Config::ENTITY_TRANSACTION);
        $collection->getSelect()->joinLeft(
            array('trnx' => $tableTrnx),
            'main_table.id = trnx.operation_id',
            '*'
        );
        $fldDate = 'trnx.' . Transaction::ATTR_DATE_APPLIED;
        $from = Config::get()->helperPeriod()->calcPeriodFromTs($periodValue, $periodCode);
        $to = Config::get()->helperPeriod()->calcPeriodToTs($periodValue, $periodCode);
        $collection->addFieldToFilter($fldDate, array('gteq' => $from));
        $collection->addFieldToFilter($fldDate, array('lteq' => $to));
        $sql = $collection->getSelectSql(true);
        $result->setCollection($collection);
        $result->setErrorCode(GetOperationsForPvWriteOffResponse::ERR_NO_ERROR);
        return $result;
    }

    private function _getTypesForPvWriteOff()
    {
        $result = array();
        $result[] = $this->_helperType->getOperId(Config::OPER_ORDER_PV);
        $result[] = $this->_helperType->getOperId(Config::OPER_PV_INT);
        $result[] = $this->_helperType->getOperId(Config::OPER_PV_FWRD);
        return $result;
    }

    /**
     * @param Praxigento_Bonus_Service_Operations_Request_CreateOperationPvWriteOff $req
     * @return Praxigento_Bonus_Service_Operations_Response_CreateOperationPvWriteOff
     */
    public function createOperationPvWriteOff(CreateOperationPvWriteOffRequest $req)
    {
        /** @var  $result CreateOperationPvWriteOffResponse */
        $result = Mage::getModel('prxgt_bonus_service/operations_response_createOperationPvWriteOff');

        return $result;
    }

    /**
     * @return CreateOperationPvWriteOffRequest
     */
    public function requestCreateOperationPvWriteOff()
    {
        $result = Mage::getModel('prxgt_bonus_service/operations_request_createOperationPvWriteOff');
        return $result;
    }

    /**
     * @return Praxigento_Bonus_Service_Operations_Request_GetOperationsForPvWriteOff
     */
    public function requestGetOperationsForPvWriteOff()
    {
        $result = Mage::getModel('prxgt_bonus_service/operations_request_getOperationsForPvWriteOff');
        return $result;
    }

}