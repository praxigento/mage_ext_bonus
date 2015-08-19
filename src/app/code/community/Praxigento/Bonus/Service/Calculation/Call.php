<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;
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
    extends Praxigento_Bonus_Service_Base_Call
{
    /**
     * Perform one iteration of the PV Write Off calculation.
     *
     * @param Praxigento_Bonus_Service_Calculation_Request_CalcPvWriteOff $req
     * @return Praxigento_Bonus_Service_Calculation_Request_CalcPvWriteOff
     */
    public function calcPvWriteOff(CalcPvWriteOffRequest $req)
    {
        /** @var  $result CalcPvWriteOffRequest */
        $result = Mage::getModel('prxgt_bonus_service/calculation_response_calcPvWriteOff');
        if ($this->_helper->cfgPersonalBonusEnabled()) {
            /** @var  $call Praxigento_Bonus_Service_Period_Call */
            $call = Config::get()->servicePeriod();
            /** @var  $resp  GetPeriodForPvWriteOffResponse */
            $resp = $call->getPeriodForPvWriteOff();
            if ($resp->isSucceed()) {
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
                $logCalc = $respR->getLogCalc();
                /* select all operation for period */
                $callOp = Config::get()->serviceOperations();
                /** @var  $reqOpGet GetOperationsForPvWriteOffRequest */
                $reqOpGet = $callOp->requestGetOperationsForPvWriteOff();
                $reqOpGet->setPeriodValue($periodValue);
                $reqOpGet->setPeriodCode($resp->getPeriodType());
                $reqOpGet->setLogCalcId($logCalc->getId());
                /** @var  $respOpGet GetOperationsForPvWriteOffResponse */
                $respOpGet = $callOp->getOperationsForPvWriteOff($reqOpGet);
                $opers = $respOpGet->getCollection();
                /* for all operations we need calculate period balances and create PV write off operations. */
                $balance = array();
                foreach ($opers as $item) {
                    $debitAccId = $item->getData('debit_acc_id');
                    $creditAccId = $item->getData('credit_acc_id');
                    $value = $item->getData('value');
                    if (isset($balance[$debitAccId])) {
                        $balance[$debitAccId] -= $value;
                    } else {
                        $balance[$debitAccId] = -$value;
                    }
                    if (isset($balance[$creditAccId])) {
                        $balance[$creditAccId] += $value;
                    } else {
                        $balance[$creditAccId] = $value;
                    }
                }
                /* update balances */
                $dateApplied = Config::get()->helperPeriod()->calcPeriodToTs($periodValue, $resp->getPeriodType());
                $accountantAccId = Config::get()->helperAccount()->getAccountantAccByAssetCode(Config::ASSET_PV);

                foreach ($balance as $accId => $val) {
                    if ($accId == $accountantAccId) continue;
                    $reqOp = $callOp->requestCreateOperationPvWriteOff();
                    $reqOp->setCustomerAccountId($accId);
                    $reqOp->setValue($val);
                    $reqOp->setDateApplied($dateApplied);
                    $respOp = $callOp->createOperationPvWriteOff($reqOp);
                    if ($respOp->isSucceed()) {
                        // continue
                    } else {
                        // ???
                    }
                }
                /* mark period as processed */
                $logCalc->setState(Config::STATE_PERIOD_COMPLETE);
                $logCalc->getResource()->save($logCalc);


            } else {
                if ($resp->getErrorCode() == GetPeriodForWriteOff::ERR_NOTHING_TO_DO) {
                    $this->_log->warn("There are no periods/operations to calculate PV Write Off.");
                    if ($resp->isNewPeriod()) {
                    } else {
                    }
                } else {
                    $this->_log->warn("Cannot get period to calculate PV Write Off.");
                }
            }
        } else {
            $this->_log->debug("Personal bonus is disabled. PV Write Off calculation cannot be started.");
        }
        return $result;
    }

    /**
     * @return Praxigento_Bonus_Service_Calculation_Response_CalcPvWriteOff
     */
    public function requestCalcPvWriteOff()
    {
        $result = Mage::getModel('prxgt_bonus_service/calculation_response_calcPvWriteOff');
        return $result;
    }
}
