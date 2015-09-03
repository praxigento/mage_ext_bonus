<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;
use Praxigento_Bonus_Model_Own_Log_Calc as LogCalc;
use Praxigento_Bonus_Model_Own_Period as Period;
use Praxigento_Bonus_Model_Own_Transaction as Transaction;
use Praxigento_Bonus_Service_Calculation_Request_CalcPvWriteOff as CalcPvWriteOffRequest;
use Praxigento_Bonus_Service_Calculation_Response_CalcPvWriteOff as CalcPvWriteOffResponse;
use Praxigento_Bonus_Service_Operations_Request_GetOperationsForPvWriteOff as GetOperationsForPvWriteOffRequest;
use Praxigento_Bonus_Service_Operations_Response_GetOperationsForPvWriteOff as GetOperationsForPvWriteOffResponse;
use Praxigento_Bonus_Service_Period_Request_GetPeriodForPvWriteOff as GetPeriodForPvWriteOffRequest;
use Praxigento_Bonus_Service_Period_Response_GetPeriodForPvWriteOff as GetPeriodForPvWriteOffResponse;

/**
 * Perform various calculations.
 *
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Service_Calculation_Call
    extends Praxigento_Bonus_Service_Base_Call {
    /**
     * Perform one iteration of the PV Write Off calculation.
     *
     * @param Praxigento_Bonus_Service_Calculation_Request_CalcPvWriteOff $req
     *
     * @return Praxigento_Bonus_Service_Calculation_Response_CalcPvWriteOff
     */
    public function calcPvWriteOff(CalcPvWriteOffRequest $req) {
        /** @var  $result CalcPvWriteOffResponse */
        $result = Mage::getModel('prxgt_bonus_service/calculation_response_calcPvWriteOff');
        if($this->_helper->cfgPersonalBonusEnabled()) {
            /** @var  $call Praxigento_Bonus_Service_Period_Call */
            $call = Config::get()->servicePeriod();
            /** @var  $resp  GetPeriodForPvWriteOffResponse */
            $resp = $call->getPeriodForPvWriteOff();
            if($resp->isSucceed()) {
                $periodValue = $resp->getPeriodValue();
                $periodCode  = $resp->getPeriodTypeCode();
                $this->_log->debug("'PV WriteOff' calculations for period '$periodValue' is started.");
                /* prepare data to register or load period calculation */
                $reqR = $call->requestRegisterPeriodCalculation();
                $reqR->setPeriodId($resp->getExistingPeriodId());
                $reqR->setLogCalcId($resp->getExistingLogCalcId());
                $reqR->setPeriodValue($periodValue);
                $reqR->setTypeCalcId($resp->getCalculationTypeId());
                $reqR->setTypePeriodId($resp->getPeriodTypeId());
                $respR   = $call->registerPeriodCalculation($reqR);
                $period  = $respR->getPeriod();
                $logCalc = $respR->getLogCalc();
                /* select all operation for period */
                $callOp = Config::get()->serviceOperations();
                /** @var  $reqOpGet GetOperationsForPvWriteOffRequest */
                $reqOpGet = $callOp->requestGetOperationsForPvWriteOff();
                $reqOpGet->setPeriodValue($periodValue);
                $reqOpGet->setPeriodCode($periodCode);
                $reqOpGet->setLogCalcId($logCalc->getId());
                /** @var  $respOpGet GetOperationsForPvWriteOffResponse */
                $respOpGet = $callOp->getOperationsForPvWriteOff($reqOpGet);
                $opers     = $respOpGet->getCollection();
                /* process found operations, create PvWriteOff operations, update balances and complete period */
                $this->_pvWriteOffProcessOperations($opers, $periodValue, $periodCode);
                /* mark period as processed */
                $logCalc->setState(Config::STATE_PERIOD_COMPLETE);
                $logCalc->getResource()->save($logCalc);
                $result->setErrorCode(CalcPvWriteOffResponse::ERR_NO_ERROR);
            } else {
                if($resp->getErrorCode() == GetPeriodForPvWriteOffResponse::ERR_NOTHING_TO_DO) {
                    $this->_log->warn('There are no operations to calculate PV Write Off.');
                    if($resp->isNewPeriod()) {
                        /* we need registry PV Write Off calc for empty periods. */
                        $periodValue = $resp->getPeriodValue();
                        $this->_log->debug("'PV WriteOff' calculations for period '$periodValue' is started.");
                        /* prepare data to register or load period calculation */
                        $reqR = $call->requestRegisterPeriodCalculation();
                        $reqR->setPeriodId($resp->getExistingPeriodId());
                        $reqR->setLogCalcId($resp->getExistingLogCalcId());
                        $reqR->setPeriodValue($periodValue);
                        $reqR->setTypeCalcId($resp->getCalculationTypeId());
                        $reqR->setTypePeriodId($resp->getPeriodTypeId());
                        $respR = $call->registerPeriodCalculation($reqR);
                        /* mark period as processed */
                        $logCalc = $respR->getLogCalc();
                        $logCalc->setState(Config::STATE_PERIOD_COMPLETE);
                        $logCalc->getResource()->save($logCalc);
                    }
                    $result->setErrorCode(CalcPvWriteOffResponse::ERR_NO_ERROR);
                } else {
                    $this->_log->error("Cannot get period to calculate PV Write Off.");
                }
            }
        } else {
            $this->_log->warn('Personal bonus is disabled. PV Write Off calculation cannot be started.');
        }
        return $result;
    }

    /**
     * We need to calculate period balances and to create PV write off operations for all elated operations
     * we are found.
     */
    private function _pvWriteOffProcessOperations(
        Praxigento_Bonus_Resource_Own_Operation_Collection $opers,
        $periodValue,
        $periodCode
    ) {
        $callOp  = Config::get()->serviceOperations();
        $changes = array();
        foreach($opers as $item) {
            $debitAccId  = $item->getData(Transaction::ATTR_DEBIT_ACC_ID);
            $creditAccId = $item->getData(Transaction::ATTR_CREDIT_ACC_ID);
            $value       = $item->getData(Transaction::ATTR_VALUE);
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
        $dateApplied     = $this->_helperPeriod->calcPeriodToTs($periodValue, $periodCode);
        $accountantAccId = $this->_helperAccount->getAccountantAccByAssetCode(Config::ASSET_PV);

        foreach($changes as $accId => $val) {
            /* skip Store itself account (it is counterparty of all other transactions) */
            if($accId == $accountantAccId) {
                continue;
            }
            $reqOp = $callOp->requestCreateOperationPvWriteOff();
            $reqOp->setCustomerAccountId($accId);
            $reqOp->setValue($val);
            $reqOp->setDateApplied($dateApplied);
            $respOp = $callOp->createOperationPvWriteOff($reqOp);
            if(!$respOp->isSucceed()) {
                $errCode = $respOp->getErrorCode();
                Mage::throwException("Cannot create PV Write Off operation for customer acc #$accId "
                                     . "(value=$val, date=$dateApplied). Error code: $errCode");
            }
        }
        return;
    }

    /**
     * @return Praxigento_Bonus_Service_Calculation_Response_CalcPvWriteOff
     */
    public function requestCalcPvWriteOff() {
        $result = Mage::getModel('prxgt_bonus_service/calculation_response_calcPvWriteOff');
        return $result;
    }
}
