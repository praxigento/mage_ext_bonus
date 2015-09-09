<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;
use Praxigento_Bonus_Model_Own_Transaction as Transaction;
use Praxigento_Bonus_Service_Operations_Response_GetOperationsForPvWriteOff as GetOperationsForPvWriteOffResponse;

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Service_Calculation_Hndl_WriteOffOperations {
    /** @var Praxigento_Bonus_Helper_Account */
    private $_helperAccount;
    /** @var Praxigento_Bonus_Helper_Period */
    private $_helperPeriod;

    /**
     * Praxigento_Bonus_Service_Calculation_Hndl_WriteOffOperations constructor.
     */
    public function __construct() {
        $this->_helperAccount = Config::get()->helperAccount();
        $this->_helperPeriod = Config::get()->helperPeriod();
    }

    /**
     * We should calculate period balances and create PV write off operations for all related operations
     * we are found.
     *
     * @param Varien_Data_Collection $opers
     * @param                        $periodValue
     * @param                        $periodCode
     */
    public function process(
        GetOperationsForPvWriteOffResponse $resp,
        $periodValue,
        $periodCode
    ) {
        $opers = $resp->getCollection();
        /* Loop all operations and compute values for PVWrite Off operations */
        $changes = array();
        foreach($opers as $item) {
            $debitAccId = $item->getData(GetOperationsForPvWriteOffResponse::TRN_DEBIT_ACC_ID);
            $creditAccId = $item->getData(GetOperationsForPvWriteOffResponse::TRN_CREDIT_ACC_ID);
            $value = $item->getData(GetOperationsForPvWriteOffResponse::TRN_VALUE);
            if(isset($changes[ $debitAccId ])) {
                $changes[ $debitAccId ] -= $value;
            } else {
                $changes[ $debitAccId ] = -$value;
            }
            if(isset($changes[ $creditAccId ])) {
                $changes[ $creditAccId ] += $value;
            } else {
                $changes[ $creditAccId ] = $value;
            }
        }
        /* Create PvWriteOff operations with the last second of the period and update NOW balances. */
        $dateApplied = $this->_helperPeriod->calcPeriodToTs($periodValue, $periodCode);
        $accountantAccId = $this->_helperAccount->getAccountantAccIdByAssetCode(Config::ASSET_PV);
        /** @var  $callOp Praxigento_Bonus_Service_Operations_Call */
        $callOp = Config::get()->serviceOperations();
        foreach($changes as $accId => $val) {
            /* skip Store itself account (it is counterparty of all other transactions) */
            if($accId == $accountantAccId) {
                continue;
            }
            /** @var  $reqOp Praxigento_Bonus_Service_Operations_Request_CreateOperationPvWriteOff */
            $reqOp = $callOp->requestCreateOperationPvWriteOff();
            $reqOp->setCustomerAccountId($accId);
            $reqOp->setValue($val);
            $reqOp->setDateApplied($dateApplied);
            $respOp = $callOp->createOperationPvWriteOff($reqOp);
            if($respOp->isSucceed()) {
                continue;
            } else {
                $errCode = $respOp->getErrorCode();
                Mage::throwException("Cannot create PV Write Off operation for customer acc #$accId "
                                     . "(value=$val, date=$dateApplied). Error code: $errCode");
            }
        }
        return;
    }

}