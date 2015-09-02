<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;
use Praxigento_Bonus_Model_Own_Balance as Balance;
use Praxigento_Bonus_Model_Own_Log_Calc as LogCalc;
use Praxigento_Bonus_Model_Own_Operation as Operation;
use Praxigento_Bonus_Model_Own_Period as Period;
use Praxigento_Bonus_Model_Own_Transaction as Transaction;
use Praxigento_Bonus_Service_Operations_Request_CreateOperationPvWriteOff as CreateOperationPvWriteOffRequest;
use Praxigento_Bonus_Service_Operations_Request_CreateTransaction as CreateTransactionRequest;
use Praxigento_Bonus_Service_Operations_Request_GetOperationsForPvWriteOff as GetOperationsForPvWriteOffRequest;
use Praxigento_Bonus_Service_Operations_Request_UpdateBalance as UpdateBalanceRequest;
use Praxigento_Bonus_Service_Operations_Response_CreateOperationPvWriteOff as CreateOperationPvWriteOffResponse;
use Praxigento_Bonus_Service_Operations_Response_CreateTransaction as CreateTransactionResponse;
use Praxigento_Bonus_Service_Operations_Response_GetOperationsForPvWriteOff as GetOperationsForPvWriteOffResponse;
use Praxigento_Bonus_Service_Operations_Response_UpdateBalance as UpdateBalanceResponse;

/**
 *
 * Retrieve operations collection for various calculations.
 *
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Service_Operations_Call
    extends Praxigento_Bonus_Service_Base_Call {

    /**
     * @param Praxigento_Bonus_Service_Operations_Request_GetOperationsForPvWriteOff $req
     *
     * @return Praxigento_Bonus_Service_Operations_Response_GetOperationsForPvWriteOff
     */
    public function getOperationsForPvWriteOff(GetOperationsForPvWriteOffRequest $req) {
        $result      = Mage::getModel('prxgt_bonus_service/operations_response_getOperationsForPvWriteOff');
        $logCalcId   = $req->getLogCalcId();
        $periodValue = $req->getPeriodValue();
        $periodCode  = $req->getPeriodCode();
        /**
         * SELECT
         * `main_table`.*,
         * `trnx`.`id` AS `trn_id`,
         * `trnx`.`date_applied` AS `trn_date_applied`,
         * `trnx`.`debit_acc_id` AS `trn_debit_acc_id`,
         * `trnx`.`credit_acc_id` AS `trn_credit_acc_id`,
         * `trnx`.`value` AS `trn_value`
         * FROM `prxgt_bonus_operation` AS `main_table`
         * LEFT JOIN `prxgt_bonus_trnx` AS `trnx`
         * ON main_table.id = trnx.operation_id
         * WHERE ((type_id = '2')
         * OR (type_id = '5')
         * OR (type_id = '4'))
         * AND (trnx.date_applied >= '2015-06-01 07:00:00')
         * AND (trnx.date_applied <= '2015-06-02 06:59:59')
         */
        $collection = Config::get()->collectionOperation();
        /* filter by operations types */
        $fields  = array();
        $values  = array();
        $operIds = $this->_helperType->getOperIdsForPvWriteOff();
        foreach($operIds as $one) {
            $fields[] = Operation::ATTR_TYPE_ID;
            $values[] = $one;
        }
        $collection->addFieldToFilter($fields, $values);
        $as        = 'trnx';
        $tableTrnx = $collection->getTable(Config::CFG_MODEL . '/' . Config::ENTITY_TRANSACTION);
        $collection->getSelect()->joinLeft(
            array( $as => $tableTrnx ),
            "main_table.id = $as.operation_id",
            array(
                GetOperationsForPvWriteOffResponse::TRN_ID            => Transaction::ATTR_ID,
                GetOperationsForPvWriteOffResponse::TRN_DATA_APPLIED  => Transaction::ATTR_DATE_APPLIED,
                GetOperationsForPvWriteOffResponse::TRN_DEBIT_ACC_ID  => Transaction::ATTR_DEBIT_ACC_ID,
                GetOperationsForPvWriteOffResponse::TRN_CREDIT_ACC_ID => Transaction::ATTR_CREDIT_ACC_ID,
                GetOperationsForPvWriteOffResponse::TRN_VALUE         => Transaction::ATTR_VALUE
            )
        );
        $fldDate = $as . '.' . Transaction::ATTR_DATE_APPLIED;
        $from    = $this->_helperPeriod->calcPeriodFromTs($periodValue, $periodCode);
        $to      = $this->_helperPeriod->calcPeriodToTs($periodValue, $periodCode);
        $collection->addFieldToFilter($fldDate, array( 'gteq' => $from ));
        $collection->addFieldToFilter($fldDate, array( 'lteq' => $to ));
        $sql = $collection->getSelectSql(true);
        $result->setCollection($collection);
        $result->setErrorCode(GetOperationsForPvWriteOffResponse::ERR_NO_ERROR);
        return $result;
    }

    /**
     * @param Praxigento_Bonus_Service_Operations_Request_CreateOperationPvWriteOff $req
     *
     * @return Praxigento_Bonus_Service_Operations_Response_CreateOperationPvWriteOff
     */
    public function createOperationPvWriteOff(CreateOperationPvWriteOffRequest $req) {
        /** @var  $result CreateOperationPvWriteOffResponse */
        $result = Mage::getModel('prxgt_bonus_service/operations_response_createOperationPvWriteOff');
        /* DB transaction */
        $conn = Config::get()->connectionWrite();

        $customerAccId   = $req->getCustomerAccountId();
        $value           = $req->getValue();
        $dateApplied     = $req->getDateApplied();
        $accountantAcc   = $this->_helperAccount->getAccountantAccByAssetCode(Config::ASSET_PV);
        $accountantAccId = $accountantAcc->getId();
        $typeOperId      = Config::get()->helperType()->getOperId(Config::OPER_PV_WRITE_OFF);
        try {
            $conn->beginTransaction();
            /* create operation */
            $operation = Config::get()->modelOperation();
            $operation->setTypeId($typeOperId);
            $operation->save();
            $operationId = $operation->getId();
            /* don't create transactions for empty operations */
            if($value != 0) {
                /* create transaction */
                $reqTrn = $this->requestCreateTransaction();
                $reqTrn->setOperationId($operationId);
                $reqTrn->setDebitAccId($customerAccId);
                $reqTrn->setCreditAccId($accountantAccId);
                $reqTrn->setValue($value);
                $reqTrn->setDateApplied($dateApplied);
                $this->createTransaction($reqTrn);
            }
            $conn->commit();
            $result->setErrorCode(CreateOperationPvWriteOffResponse::ERR_NO_ERROR);
        } catch(Exception $e) {
            $conn->rollBack();
        }
        return $result;
    }

    /**
     * @param Praxigento_Bonus_Service_Operations_Request_CreateTransaction $req
     *
     * @return Praxigento_Bonus_Service_Operations_Response_CreateTransaction
     */
    public function createTransaction(CreateTransactionRequest $req) {
        /** @var  $result CreateTransactionResponse */
        $result      = Mage::getModel('prxgt_bonus_service/operations_response_createTransaction');
        $debitAccId  = $req->getDebitAccId();
        $creditAccId = $req->getCreditAccId();
        $value       = $req->getValue();
        /* DB transaction */
        $conn = Config::get()->connectionWrite();
        try {
            $conn->beginTransaction();
            /** @var  $trnx Praxigento_Bonus_Model_Own_Transaction */
            $trnx = Config::get()->modelTransaction();
            $trnx->setOperationId($req->getOperationId());
            $trnx->setDateApplied($req->getDateApplied());
            $trnx->setDebitAccId($debitAccId);
            $trnx->setCreditAccId($creditAccId);
            $trnx->setValue($value);
            $trnx->save();
            /* update balances */
            /* decrease debit */
            $reqBalance = $this->requestUpdateBalance();
            $reqBalance->setAccountId($debitAccId);
            $reqBalance->setValue(0 - $value);
            $this->updateBalance($reqBalance);
            /* increase credit */
            $reqBalance = $this->requestUpdateBalance();
            $reqBalance->setAccountId($creditAccId);
            $reqBalance->setValue($value);
            $this->updateBalance($reqBalance);
            $conn->commit();
            $result->setErrorCode(CreateTransactionResponse::ERR_NO_ERROR);
        } catch(Exception $e) {
            $conn->rollBack();
            $msg = "Cannot create transaction (debit: $debitAccId, credit: $creditAccId, amount: $value).";
            $msg .= " Reason: " . $e->getMessage();
            $this->_log->error($msg);
        }
        return $result;
    }

    /**
     * @param Praxigento_Bonus_Service_Operations_Request_UpdateBalance $req
     *
     * @return Praxigento_Bonus_Service_Operations_Response_UpdateBalance
     */
    public function updateBalance(UpdateBalanceRequest $req) {
        /** @var  $result UpdateBalanceResponse */
        $result    = Mage::getModel('prxgt_bonus_service/operations_response_updateBalance');
        $accountId = $req->getAccountId();
        $period    = $req->getPeriod();
        /** @var  $balanceCollection Praxigento_Bonus_Resource_Own_Balance_Collection */
        $balanceCollection = Config::get()->collectionBalance();
        $balanceCollection->addFieldToFilter(Balance::ATTR_ACCOUNT_ID, $accountId);
        $balanceCollection->addFieldToFilter(Balance::ATTR_PERIOD, $period);
        /** @var  $balance Praxigento_Bonus_Model_Own_Balance */
        $balance = Config::get()->modelBalance();
        if($balanceCollection->getSize()) {
            $balance = $balanceCollection->getFirstItem();
        } else {
            /* create new balance record for NOW  */
            $balance->setAccountId($accountId);
            $balance->setPeriod($period);
        }
        $val = $balance->getValue() + $req->getValue();
        $balance->setValue($val);
        $balance->save();
        $result->setBalance($balance);
        $result->setErrorCode(UpdateBalanceResponse::ERR_NO_ERROR);
        return $result;
    }

    /**
     * @return CreateOperationPvWriteOffRequest
     */
    public function requestCreateOperationPvWriteOff() {
        $result = Mage::getModel('prxgt_bonus_service/operations_request_createOperationPvWriteOff');
        return $result;
    }

    /**
     * @return Praxigento_Bonus_Service_Operations_Request_GetOperationsForPvWriteOff
     */
    public function requestGetOperationsForPvWriteOff() {
        $result = Mage::getModel('prxgt_bonus_service/operations_request_getOperationsForPvWriteOff');
        return $result;
    }

    /**
     * @return Praxigento_Bonus_Service_Operations_Request_CreateTransaction
     */
    public function requestCreateTransaction() {
        $result = Mage::getModel('prxgt_bonus_service/operations_request_createTransaction');
        return $result;
    }

    /**
     * @return Praxigento_Bonus_Service_Operations_Request_UpdateBalance
     */
    public function requestUpdateBalance() {
        $result = Mage::getModel('prxgt_bonus_service/operations_request_updateBalance');
        return $result;
    }

}