<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;
use Praxigento_Bonus_Service_Period_Request_GetPeriodForPersonalBonus as GetPeriodForPersonalBonusRequest;
use Praxigento_Bonus_Service_Period_Response_GetPeriodForPersonalBonus as GetPeriodForPersonalBonusResponse;

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
    public function getPeriodCollection()
    {
        $result = Mage::getModel('prxgt_bonus_model/period')->getCollection();
        return $result;
    }

    /**
     * This method is mocked in unit tests.
     *
     * @return Praxigento_Bonus_Resource_Own_Transaction_Collection
     */
    public function getTransactionCollection()
    {
        $result = Mage::getModel('prxgt_bonus_model/transaction')->getCollection();
        return $result;
    }

    /**
     * @param Praxigento_Bonus_Service_Period_Request_GetPeriodForPersonalBonus $req
     * @return Praxigento_Bonus_Service_Period_Response_GetPeriodForPersonalBonus
     */
    public function getPeriodForPersonalBonus(GetPeriodForPersonalBonusRequest $req)
    {
        /** @var  $result GetPeriodForPersonalBonusResponse */
        $result = Mage::getModel(Config::CFG_SERVICE . '/period_response_getPeriodForPersonalBonus');
        /* shortcuts for request parameters */
        $periodTypeId = $req->periodTypeId;
        $bonusTypeId = $req->bonusTypeId;
        $periodCode = $req->periodCode;
        $operTypeIds = $req->operationTypeIds;
        /* get period in 'processing' state */
        $periods = $this->getPeriodCollection();
        $periods->addFieldToFilter(Praxigento_Bonus_Model_Own_Period::ATTR_BONUS_ID, $bonusTypeId);
        $periods->addFieldToFilter(Praxigento_Bonus_Model_Own_Period::ATTR_TYPE, $periodTypeId);
        $periods->addFieldToFilter(Praxigento_Bonus_Model_Own_Period::ATTR_STATE, Config::STATE_PERIOD_PROCESSING);
        // WHERE (bonus_id = '1') AND (type = '3') AND (state = 'processing')
        if ($periods->getSize()) {
            /* there is desired period in 'processing' state */
            /** @var  $item Praxigento_Bonus_Model_Own_Period */
            $item = $periods->getFirstItem();
            $result->setErrorCode(GetPeriodForPersonalBonusResponse::ERR_NO_ERROR);
            $result->periodValue = $item->getValue();
        } else {
            /* get the last period in 'complete' status */
            $periods = $this->getPeriodCollection();
            $periods->addFieldToFilter(Praxigento_Bonus_Model_Own_Period::ATTR_TYPE, $bonusTypeId);
            $periods->addFieldToFilter(Praxigento_Bonus_Model_Own_Period::ATTR_TYPE, $periodTypeId);
            $periods->addFieldToFilter(Praxigento_Bonus_Model_Own_Period::ATTR_STATE, Config::STATE_PERIOD_COMPLETE);
            $periods->addOrder(Praxigento_Bonus_Model_Own_Period::ATTR_ID, Varien_Data_Collection::SORT_ORDER_ASC);
            $sql = (string)$periods->getSelectSql();
            if ($periods->getSize()) {
                $periodLast = $periods->getFirstItem();
                $periodValue = Mage::helper(Praxigento_Bonus_Config::CFG_HELPER_PERIOD)
                    ->calcPeriodNext($periodLast->getValue(), $periodCode);
            } else {
                /* get transaction with minimal date_applied and operation type = ORDR_PV or PV_INT */
                $collection = $this->getTransactionCollection();
                $asOper = 'o';
                $table = array($asOper => Config::CFG_MODEL . '/' . Config::ENTITY_OPERATION);
                $cond = 'main_table.' . Praxigento_Bonus_Model_Own_Transaction::ATTR_OPERATION_ID . '='
                    . $asOper . '.' . Praxigento_Bonus_Model_Own_Operation::ATTR_ID;
                $collection->join($table, $cond);
                /* add filter by operation types */
                $fields = array();
                $opTypes = array();
                foreach ($operTypeIds as $one) {
                    $fields[] = $asOper . '.' . Praxigento_Bonus_Model_Own_Operation::ATTR_TYPE_ID;
                    $opTypes[] = $one;
                }
                $collection->addFieldToFilter($fields, $opTypes);
                $collection->setOrder(
                    Praxigento_Bonus_Model_Own_Transaction::ATTR_DATE_APPLIED,
                    Varien_Data_Collection::SORT_ORDER_ASC
                );
                $sql = (string)$collection->getSelectSql();
                $item = $collection->getFirstItem();
                $dateApplied = $item->getData(Praxigento_Bonus_Model_Own_Transaction::ATTR_DATE_APPLIED);
                $periodValue = Mage::helper(Praxigento_Bonus_Config::CFG_HELPER_PERIOD)->calcPeriodCurrent($dateApplied, $periodCode);
            }
        }
        return $result;
    }
}