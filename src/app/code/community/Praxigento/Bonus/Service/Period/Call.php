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
use Praxigento_Bonus_Service_Period_Request_GetPeriodForPersonalBonus as GetPeriodForPersonalBonusRequest;
use Praxigento_Bonus_Service_Period_Request_GetPeriodForPvWriteOff as GetPeriodForPvWriteOffRequest;
use Praxigento_Bonus_Service_Period_Request_RegisterPeriodCalculation as RegisterPeriodCalculationRequest;
use Praxigento_Bonus_Service_Period_Response_GetPeriodForPersonalBonus as GetPeriodForPersonalBonusResponse;
use Praxigento_Bonus_Service_Period_Response_GetPeriodForPvWriteOff as GetPeriodForPvWriteOffResponse;
use Praxigento_Bonus_Service_Period_Response_RegisterPeriodCalculation as RegisterPeriodCalculationResponse;

/**
 *
 * Calculate periods for various calculations.
 *
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Service_Period_Call
    extends Praxigento_Bonus_Service_Base_Call
{
    const AS_LOG_ID = 'log_calc_id';

    /**
     * @param Praxigento_Bonus_Service_Period_Request_GetPeriodForPvWriteOff $req
     * @return Praxigento_Bonus_Service_Period_Response_GetPeriodForPvWriteOff
     */
    public function getPeriodForPvWriteOff(GetPeriodForPvWriteOffRequest $req = null)
    {
        /** @var  $result GetPeriodForPvWriteOffResponse */
        $result = Mage::getModel('prxgt_bonus_service/period_response_getPeriodForPvWriteOff');
        /* define parameters for lookup */
        $calcTypeId = $this->_helperType->getCalcId(Config::CALC_PV_WRITE_OFF);
        $result->setCalculationTypeId($calcTypeId);
        $periodCode = $this->_helper->cfgPersonalBonusPeriod();
        $result->setPeriodType($periodCode);
        $periodTypeId = $this->_helperType->getPeriodId($periodCode);
        $result->setPeriodTypeId($periodTypeId);
        $operTypeIds = $this->_helperType->getOperIdsForPvWriteOff();
        /* get period in 'processing' state */
        $asLog = 'log';
        $periods = $this->_getCalcPeriodsCollection($asLog);
        $periods->addFieldToFilter(Period::ATTR_CALC_TYPE_ID, $calcTypeId);
        $periods->addFieldToFilter(Period::ATTR_TYPE, $periodTypeId);
        $periods->addFieldToFilter($asLog . '.' . LogCalc::ATTR_STATE, Config::STATE_PERIOD_PROCESSING);
        $sql = $periods->getSelectSql(true);
        // WHERE (calc_type_id = '8') AND (type = '1') AND (log.state = 'processing')
        if ($periods->getSize()) {
            /* there is desired period in 'processing' state */
            /** @var  $item Praxigento_Bonus_Model_Own_Period */
            $item = $periods->getFirstItem();
            $id = $item->getData(Period::ATTR_ID);
            $value = $item->getData(Period::ATTR_VALUE);
            $result->setExistingPeriodId($id);
            $result->setExistingLogCalcId($item->getData(self::AS_LOG_ID));
            $result->setPeriodValue($value);
            $result->setErrorCode(GetPeriodForPersonalBonusResponse::ERR_NO_ERROR);
        } else {
            /* get the last period in 'complete' status */
            $periods = $this->_getCalcPeriodsCollection($asLog);
            $periods->addFieldToFilter(Period::ATTR_CALC_TYPE_ID, $calcTypeId);
            $periods->addFieldToFilter(Period::ATTR_TYPE, $periodTypeId);
            $periods->addFieldToFilter($asLog . '.' . LogCalc::ATTR_STATE, Config::STATE_PERIOD_COMPLETE);
            $periods->addOrder(Period::ATTR_ID, Varien_Data_Collection::SORT_ORDER_DESC);
            $periods->setPageSize(1);
            $sql = (string)$periods->getSelectSql();
            // WHERE (type = '8') AND (type = '1') AND (log.state = 'complete')
            if ($periods->getSize()) {
                $periodLast = $periods->getFirstItem();
                $value = $periodLast->getData(Period::ATTR_VALUE);
                $next = $this->_helperPeriod->calcPeriodNext($value, $periodCode);
                $result->setPeriodValue($next);
                // TODO add period ID & log calc ID to response
                $result->setErrorCode(GetPeriodForPersonalBonusResponse::ERR_NO_ERROR);
            } else {
                /* get transaction with minimal date_applied and operation type = ORDR_PV or PV_INT */
                $collection = $this->initTransactionCollection();
                $asOper = 'o';
                $table = array($asOper => Config::CFG_MODEL . '/' . Config::ENTITY_OPERATION);
                $cond = 'main_table.' . Transaction::ATTR_OPERATION_ID . '='
                    . $asOper . '.' . Operation::ATTR_ID;
                $collection->join($table, $cond);
                /* add filter by operation types */
                $fields = array();
                $opTypes = array();
                foreach ($operTypeIds as $one) {
                    $fields[] = $asOper . '.' . Operation::ATTR_TYPE_ID;
                    $opTypes[] = $one;
                }
                $collection->addFieldToFilter($fields, $opTypes);
                $collection->setOrder(
                    Transaction::ATTR_DATE_APPLIED,
                    Varien_Data_Collection::SORT_ORDER_ASC
                );
                $sql = (string)$collection->getSelectSql();
                if ($collection->getSize()) {
                    $item = $collection->getFirstItem();
                    $dateApplied = $item->getData(Transaction::ATTR_DATE_APPLIED);
                    $result->setPeriodValue($this->_helperPeriod->calcPeriodCurrent($dateApplied, $periodCode));
                    $result->setErrorCode(GetPeriodForPersonalBonusResponse::ERR_NO_ERROR);
                } else {
                    /* there is no transactions, nothing to do */
                    $result->setErrorCode(GetPeriodForPersonalBonusResponse::ERR_NOTHING_TO_DO);
                }
            }
        }
        return $result;
    }

    /**
     * Register calculation period or load existing data.
     *
     * @param Praxigento_Bonus_Service_Period_Request_RegisterPeriodCalculation $req
     * @return Praxigento_Bonus_Service_Period_Response_RegisterPeriodCalculation
     */
    public function registerPeriodCalculation(RegisterPeriodCalculationRequest $req)
    {
        $result = new RegisterPeriodCalculationResponse();
        $period = Config::get()->modelPeriod();
        $logCalc = Config::get()->modelLogCalc();
        /* shortcuts for request data */
        $periodId = $req->getPeriodId();
        $periodValue = $req->getPeriodValue();
        $logCalcId = $req->getLogCalcId();
        $typeCalcId = $req->getTypeCalcId();
        $typePeriodId = $req->getTypePeriodId();
        if (is_null($periodId)) {
            /* register new calculation period into DB */
            $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
            $connection->beginTransaction();
            try {
                /* look up for existing period by calculation type and period value */
                $periods = Config::collectionPeriod();
                $periods->addFieldToFilter(Period::ATTR_CALC_TYPE_ID, $typeCalcId);
                $periods->addFieldToFilter(Period::ATTR_VALUE, $periodValue);
                if ($periods->getSize() == 0) {
                    /* add new period */
                    $period->setType($typePeriodId);
                    $period->setCalcTypeId($typeCalcId);
                    $period->setValue($periodValue);
                    $period->save();
                } else {
                    $this->_log->debug("Period '$periodValue' already exists.");
                    $period = $periods->getFirstItem();
                }
                /* add new entry to calculation log */
                $logCalc->setPeriodId($period->getId());
                $logCalc->setState(Config::STATE_PERIOD_PROCESSING);
                $logCalc->save();
                $connection->commit();
                $this->_log->debug("New period '{$period->getValue()}' (#{$period->getId()}) is registered.");
            } catch (Exception $e) {
                $connection->rollBack();
                $this->_log->error(
                    "Cannot register new calculation period ($periodValue). Error: "
                    . $e->getMessage()
                );
            }
        } else {
            /* load period data */
            $period->load($periodId);
            if (is_null($logCalcId)) {
                /* add new entry to calculation log */
                $logCalc->setPeriodId($period->getId());
                $logCalc->setState(Config::STATE_PERIOD_PROCESSING);
                $logCalc->save();
            } else {
                /* load calculation log data */
                $logCalc->load($logCalcId);
            }
        }
        $result->setPeriod($period);
        $result->setLogCalc($logCalc);
        return $result;
    }

    private function  _getCalcPeriodsCollection($as)
    {
        $result = $this->initPeriodCollection();
        $table = array($as => Config::CFG_MODEL . '/' . Config::ENTITY_LOG_CALC);
        $cond = 'main_table.' . Period::ATTR_ID . '='
            . $as . '.' . LogCalc::ATTR_PERIOD_ID;
        $cols = array(
            self::AS_LOG_ID => LogCalc::ATTR_ID,
            LogCalc::ATTR_DATE_PERFORMED,
            LogCalc::ATTR_STATE
        );
        $result->join($table, $cond, $cols);
        return $result;
    }

    /**
     * This method is mocked in unit tests.
     *
     * @return Praxigento_Bonus_Resource_Own_Period_Collection
     */
    public function initPeriodCollection()
    {
        $result = Config::get()->collectionPeriod();
        return $result;
    }

    /**
     * This method is mocked in unit tests.
     *
     * @return Praxigento_Bonus_Resource_Own_Transaction_Collection
     */
    public function initTransactionCollection()
    {
        $result = Config::get()->collectionTransaction();
        return $result;
    }

    /**
     * Get period for PV bonus calculation.
     *
     * @param Praxigento_Bonus_Service_Period_Request_GetPeriodForPersonalBonus $req
     * @return Praxigento_Bonus_Service_Period_Response_GetPeriodForPersonalBonus
     */
    public function getPeriodForPersonalBonus(GetPeriodForPersonalBonusRequest $req)
    {
        /** @var  $result GetPeriodForPersonalBonusResponse */
        $result = Mage::getModel('prxgt_bonus_service/period_response_getPeriodForPersonalBonus');
        /* shortcuts for request parameters */
        $periodTypeId = $req->getPeriodTypeId();
        $calcTypeId = $req->getCalcTypeId();
        $periodCode = $req->getPeriodCode();
        $operTypeIds = $req->getOperationTypeIds();
        /* get period in 'processing' state */
//        $periods = $this->initPeriodCollection();
//        $periods->addFieldToFilter(Period::ATTR_CALC_TYPE_ID, $bonusTypeId);
//        $periods->addFieldToFilter(Period::ATTR_TYPE, $periodTypeId);
        $periods = $this->_getCalcPeriodsCollection('log');
        // TODO add state filter
//        $periods->addFieldToFilter(Period::ATTR_STATE, Config::STATE_PERIOD_PROCESSING);
        $sql = $periods->getSelectSql(true);
        // WHERE (bonus_id = '1') AND (type = '3') AND (state = 'processing')
        if ($periods->getSize()) {
            /* there is desired period in 'processing' state */
            /** @var  $item Praxigento_Bonus_Model_Own_Period */
            $item = $periods->getFirstItem();
            $id = $item->getData(Period::ATTR_ID);
            $value = $item->getData(Period::ATTR_VALUE);
            $result->setExistingPeriodId($id);
            $result->setPeriodValue($value);
            $result->setErrorCode(GetPeriodForPersonalBonusResponse::ERR_NO_ERROR);
        } else {
            /* get the last period in 'complete' status */
            $periods = $this->initPeriodCollection();
            $periods->addFieldToFilter(Period::ATTR_CALC_TYPE_ID, $calcTypeId);
            $periods->addFieldToFilter(Period::ATTR_TYPE, $periodTypeId);
            // TODO: add filter by state
//            $periods->addFieldToFilter(Period::ATTR_STATE, Config::STATE_PERIOD_COMPLETE);
            $periods->addOrder(Period::ATTR_ID, Varien_Data_Collection::SORT_ORDER_ASC);
            $sql = (string)$periods->getSelectSql();
            if ($periods->getSize()) {
                $periodLast = $periods->getFirstItem();
                $value = $periodLast->getData(Period::ATTR_VALUE);
                $next = $this->_helperPeriod->calcPeriodNext($value, $periodCode);
                $result->setPeriodValue($next);
                $result->setErrorCode(GetPeriodForPersonalBonusResponse::ERR_NO_ERROR);
            } else {
                /* get transaction with minimal date_applied and operation type = ORDR_PV or PV_INT */
                $collection = $this->initTransactionCollection();
                $asOper = 'o';
                $table = array($asOper => Config::CFG_MODEL . '/' . Config::ENTITY_OPERATION);
                $cond = 'main_table.' . Transaction::ATTR_OPERATION_ID . '='
                    . $asOper . '.' . Operation::ATTR_ID;
                $collection->join($table, $cond);
                /* add filter by operation types */
                $fields = array();
                $opTypes = array();
                foreach ($operTypeIds as $one) {
                    $fields[] = $asOper . '.' . Operation::ATTR_TYPE_ID;
                    $opTypes[] = $one;
                }
                $collection->addFieldToFilter($fields, $opTypes);
                $collection->setOrder(
                    Transaction::ATTR_DATE_APPLIED,
                    Varien_Data_Collection::SORT_ORDER_ASC
                );
                $sql = (string)$collection->getSelectSql();
                if ($collection->getSize()) {
                    $item = $collection->getFirstItem();
                    $dateApplied = $item->getData(Transaction::ATTR_DATE_APPLIED);
                    $result->setPeriodValue($this->_helperPeriod->calcPeriodCurrent($dateApplied, $periodCode));
                    $result->setErrorCode(GetPeriodForPersonalBonusResponse::ERR_NO_ERROR);
                } else {
                    /* there is no transactions, nothing to do */
                    $result->setErrorCode(GetPeriodForPersonalBonusResponse::ERR_NOTHING_TO_DO);
                }
            }
        }
        return $result;
    }

    /**
     * Request model to be populated.
     *
     * @return Praxigento_Bonus_Service_Period_Request_GetPeriodForPvWriteOff
     */
    public function requestPeriodForPvWriteOff()
    {
        $result = Mage::getModel('prxgt_bonus_service/period_request_getPeriodForPvWriteOff');
        return $result;
    }

    /**
     * Request model to be populated.
     *
     * @return Praxigento_Bonus_Service_Period_Request_GetPeriodForPersonalBonus
     */
    public function requestPeriodForPersonalBonus()
    {
        $result = Mage::getModel('prxgt_bonus_service/period_request_getPeriodForPersonalBonus');
        return $result;
    }

    /**
     * Request model to be populated.
     *
     * @return Praxigento_Bonus_Service_Period_Request_RegisterPeriodCalculation
     */
    public function requestRegisterPeriodCalculation()
    {
        $result = Mage::getModel('prxgt_bonus_service/period_request_registerPeriodCalculation');
        return $result;
    }
}