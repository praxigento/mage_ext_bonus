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
                /* process found operations, create PvWriteOff operations, update balances and complete period */
                $hndl = new Praxigento_Bonus_Service_Calculation_Hndl_WriteOffOperations();
                $hndl->process($respOpGet, $periodValue, $periodCode);
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
     * @return Praxigento_Bonus_Service_Calculation_Response_CalcPvWriteOff
     */
    public function requestCalcPvWriteOff() {
        $result = Mage::getModel('prxgt_bonus_service/calculation_response_calcPvWriteOff');
        return $result;
    }
}
