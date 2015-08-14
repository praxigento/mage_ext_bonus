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
use Praxigento_Bonus_Service_Period_Response_GetPeriodForPersonalBonus as GetPeriodForPersonalBonusResponse;
use Praxigento_Bonus_Service_Period_Response_GetPeriodForPvWriteOff as GetPeriodForPvWriteOffResponse;

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Service_Period_Call
    extends Praxigento_Bonus_Service_Base_Call
{
    /**
     * This method is mocked in unit tests.
     *
     * @return Praxigento_Bonus_Resource_Own_Period_Collection
     */
    public function initPeriodCollection()
    {
        $result = Mage::getModel('prxgt_bonus_model/period')->getCollection();
        return $result;
    }

    /**
     * This method is mocked in unit tests.
     *
     * @return Praxigento_Bonus_Resource_Own_Transaction_Collection
     */
    public function initTransactionCollection()
    {
        $result = Mage::getModel('prxgt_bonus_model/transaction')->getCollection();
        return $result;
    }

    private function  _getCalcPeriodsCollection($as)
    {
        $result = $this->initPeriodCollection();
        $table = array($as => Config::CFG_MODEL . '/' . Config::ENTITY_LOG_CALC);
        $cond = 'main_table.' . Period::ATTR_ID . '='
            . $as . '.' . LogCalc::ATTR_PERIOD_ID;
        $result->join($table, $cond, array('log_calc_id' => LogCalc::ATTR_ID, LogCalc::ATTR_DATE_PERFORMED, LogCalc::ATTR_STATE));
        return $result;
    }

    /**
     * @param Praxigento_Bonus_Service_Period_Request_GetPeriodForPvWriteOff $req
     * @return Praxigento_Bonus_Service_Period_Response_GetPeriodForPvWriteOff
     */
    public function getPeriodForPvWriteOff(GetPeriodForPvWriteOffRequest $req)
    {
        /** @var  $result GetPeriodForPvWriteOffResponse */
        $result = Mage::getModel(Config::CFG_SERVICE . '/period_response_getPeriodForPvWriteOff');
        /* shortcuts for request parameters */
        $periodTypeId = $req->getPeriodTypeId();
        $calcTypeId = $req->getCalcTypeId();
        $periodCode = $req->getPeriodCode();
        $operTypeIds = $req->getOperationTypeIds();
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
            $result->setPeriodValue($value);
            $result->setIsNewPeriod(false);
            $result->setErrorCode(GetPeriodForPersonalBonusResponse::ERR_NO_ERROR);
        } else {
            /* get the last period in 'complete' status */
            $periods = $this->_getCalcPeriodsCollection($asLog);
            $periods->addFieldToFilter(Period::ATTR_TYPE, $calcTypeId);
            $periods->addFieldToFilter(Period::ATTR_TYPE, $periodTypeId);
            $periods->addFieldToFilter($asLog . '.' . LogCalc::ATTR_STATE, Config::STATE_PERIOD_COMPLETE);
            $periods->addOrder(Period::ATTR_ID, Varien_Data_Collection::SORT_ORDER_ASC);
            $sql = (string)$periods->getSelectSql();
            // WHERE (type = '8') AND (type = '1') AND (log.state = 'complete')
            if ($periods->getSize()) {
                $periodLast = $periods->getFirstItem();
                $value = $periodLast->getData(Period::ATTR_VALUE);
                $next = Config::helperPeriod()->calcPeriodNext($value, $periodCode);
                $result->setPeriodValue($next);
                $result->setIsNewPeriod(true);
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
                    $result->setPeriodValue(Config::helperPeriod()->calcPeriodCurrent($dateApplied, $periodCode));
                    $result->setIsNewPeriod(true);
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
     * Get period for PV bonus calculation.
     *
     * @param Praxigento_Bonus_Service_Period_Request_GetPeriodForPersonalBonus $req
     * @return Praxigento_Bonus_Service_Period_Response_GetPeriodForPersonalBonus
     */
    public function getPeriodForPersonalBonus(GetPeriodForPersonalBonusRequest $req)
    {
        /** @var  $result GetPeriodForPersonalBonusResponse */
        $result = Mage::getModel(Config::CFG_SERVICE . '/period_response_getPeriodForPersonalBonus');
        /* shortcuts for request parameters */
        $periodTypeId = $req->getPeriodTypeId();
        $bonusTypeId = $req->getCalcTypeId();
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
            $result->setIsNewPeriod(false);
            $result->setErrorCode(GetPeriodForPersonalBonusResponse::ERR_NO_ERROR);
        } else {
            /* get the last period in 'complete' status */
            $periods = $this->initPeriodCollection();
            $periods->addFieldToFilter(Period::ATTR_TYPE, $bonusTypeId);
            $periods->addFieldToFilter(Period::ATTR_TYPE, $periodTypeId);
            // TODO: add filter by state
//            $periods->addFieldToFilter(Period::ATTR_STATE, Config::STATE_PERIOD_COMPLETE);
            $periods->addOrder(Period::ATTR_ID, Varien_Data_Collection::SORT_ORDER_ASC);
            $sql = (string)$periods->getSelectSql();
            if ($periods->getSize()) {
                $periodLast = $periods->getFirstItem();
                $value = $periodLast->getData(Period::ATTR_VALUE);
                $next = Config::helperPeriod()->calcPeriodNext($value, $periodCode);
                $result->setPeriodValue($next);
                $result->setIsNewPeriod(true);
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
                    $result->setPeriodValue(Config::helperPeriod()->calcPeriodCurrent($dateApplied, $periodCode));
                    $result->setIsNewPeriod(true);
                    $result->setErrorCode(GetPeriodForPersonalBonusResponse::ERR_NO_ERROR);
                } else {
                    /* there is no transactions, nothing to do */
                    $result->setErrorCode(GetPeriodForPersonalBonusResponse::ERR_NOTHING_TO_DO);
                }
            }
        }
        return $result;
    }
}