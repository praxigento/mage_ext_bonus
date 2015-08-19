<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */

/**
 * Constants for the module (hardcoded configuration).
 *
 * CFG_ - etc/config.xml related constants
 * CFG_ENTITY_ - name for entities in "/global/models/prxgt_bonus_resource/entities" node;
 *
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Config
{
    /**
     * Available assets codes.
     */

    const ASSET_EXT = 'EXT';
    const ASSET_INT = 'INT'; // Retail bonus on hold (before transfer to customer internal or external account)
    const ASSET_PV = 'PV'; // internal money account (base currency)
    const ASSET_RETAIL = 'RETAIL'; // external money account (base currency)

    /**
     * Available calculations codes.
     */

    const CALC_BONUS_COURTESY = 'BON_COURTESY';
    const CALC_BONUS_GROUP = 'BON_GROUP';
    const CALC_BONUS_INFINITY = 'BON_INFINITY';
    const CALC_BONUS_OVERRIDE = 'BON_OVERRIDE';
    const CALC_BONUS_PERSONAL = 'BON_PERSONAL';
    const CALC_BONUS_RETAIL = 'BON_RETAIL';
    const CALC_BONUS_TEAM = 'BON_TEAM';
    const CALC_PV_WRITE_OFF = 'PV_WRITE_OFF';

    /**
     * 'config.xml' related constants.
     */

    const CFG_BLOCK = 'prxgt_bonus_block';
    const CFG_HELPER = 'prxgt_bonus_helper';
    const CFG_MODEL = 'prxgt_bonus_model';
    const CFG_SERVICE = 'prxgt_bonus_service';

    /**
     * Entities in config.xml:/config/global/models/prxgt_bonus_resource/entities
     */

    const ENTITY_ACCOUNT = 'account';
    const ENTITY_BALANCE = 'balance';
    const ENTITY_CFG_PERSONAL = 'cfg_personal';
    const ENTITY_CORE_TYPE = 'core_type';
    const ENTITY_DETAILS_RETAIL = 'details_retail';
    const ENTITY_LOG_ACCOUNT = 'log_account';
    const ENTITY_LOG_CALC = 'log_calc';
    const ENTITY_LOG_DOWNLINE = 'log_downline';
    const ENTITY_LOG_ORDER = 'log_order';
    const ENTITY_LOG_PAYOUT = 'log_payout';
    const ENTITY_OPERATION = 'operation';
    const ENTITY_PERIOD = 'period';
    const ENTITY_SNAP_BONUS = 'snap_bonus';
    const ENTITY_SNAP_DOWNLINE = 'snap_downline';
    const ENTITY_TRANSACTION = 'transaction';
    const ENTITY_TYPE_ASSET = 'type_asset';
    const ENTITY_TYPE_CALC = 'type_calc';
    const ENTITY_TYPE_OPER = 'type_oper';
    const ENTITY_TYPE_PERIOD = 'type_period';

    /**
     * Formats
     */

    const FROMAT_DATETIME_SQL = 'Y-m-d H:i:s';

    /**
     * Available operations codes.
     */

    const OPER_BONUS_PV = 'BON_PV';
    const OPER_ORDER_PV = 'ORDR_PV';
    const OPER_ORDER_RETAIL = 'ORDR_RETAIL';
    const OPER_PV_FWRD = 'PV_FWRD';
    const OPER_PV_INT = 'PV_INT';
    const OPER_PV_WRITE_OFF = 'PV_WRITE_OFF';
    const OPER_TRANS_EXT_IN = 'TRANS_EXT_IN';
    const OPER_TRANS_EXT_OUT = 'TRANS_EXT_OUT';
    const OPER_TRANS_INT = 'TRANS_INT';

    /**
     * Available bonus calculation periods.
     */

    const PERIOD_DAY = 'DAY';
    const PERIOD_KEY_NOW = 'NOW';
    const PERIOD_MONTH = 'MONTH';
    const PERIOD_WEEK = 'WEEK';
    const PERIOD_YEAR = 'YEAR';

    /**
     * Available states for bonus calculation periods.
     */

    const STATE_PERIOD_COMPLETE = 'complete';
    const STATE_PERIOD_PLACED = 'placed';
    const STATE_PERIOD_PROCESSING = 'processing';
    const STATE_PERIOD_REVERTED = 'reverted';

    /**
     * Itself. Singleton.
     * We should not use static methods (bad testability).
     *
     * @var Praxigento_Bonus_Config
     */
    private static $_instance;

    /**
     * Praxigento_Bonus_Config constructor.
     */
    public function __construct()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = $this;
        }
    }

    /**
     * Get singleton instance.
     *
     * @return Praxigento_Bonus_Config
     */
    public static function  get()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new Praxigento_Bonus_Config();
        }
        return self::$_instance;
    }

    /**
     * Set test unit instance with mocked methods.
     *
     * @param Praxigento_Bonus_Config $instance
     */
    public static function  set(Praxigento_Bonus_Config $instance)
    {
        self::$_instance = $instance;
    }

    /**
     * @return  \Praxigento_Bonus_Helper_Data
     */
    public function helper()
    {
        $result = Mage::helper('prxgt_bonus_helper');
        return $result;
    }

    /**
     * @return Nmmlm_Core_Helper_Data
     */
    public function helperCore()
    {
        $result = Mage::helper('nmmlm_core_helper');
        return $result;
    }

    /**
     * @return  \Praxigento_Bonus_Helper_Period
     */
    public function helperPeriod()
    {
        $result = Mage::helper('prxgt_bonus_helper/period');
        return $result;
    }

    /**
     * @return  \Praxigento_Bonus_Helper_Account
     */
    public function helperAccount()
    {
        $result = Mage::helper('prxgt_bonus_helper/account');
        return $result;
    }

    /**
     * @return  \Praxigento_Bonus_Helper_Type
     */
    public function helperType()
    {
        $result = Mage::helper('prxgt_bonus_helper/type');
        return $result;
    }

    /**
     * @return Praxigento_Bonus_Resource_Own_Period_Collection
     */
    public function collectionPeriod()
    {
        $result = self::modelPeriod()->getCollection();
        return $result;
    }

    /**
     * @return Praxigento_Bonus_Model_Own_Period
     */
    public function modelPeriod()
    {
        $result = Mage::getModel('prxgt_bonus_model/period');
        return $result;
    }

    /**
     * @return Praxigento_Bonus_Model_Own_Log_Calc
     */
    public function modelLogCalc()
    {
        $result = Mage::getModel('prxgt_bonus_model/log_calc');
        return $result;
    }

    /**
     * @return Praxigento_Bonus_Resource_Own_Log_Calc_Collection
     */
    public function collectionLogCalc()
    {
        $result = self::modelLogCalc()->getCollection();
        return $result;
    }

    /**
     * @return Praxigento_Bonus_Resource_Own_Account_Collection
     */
    public function collectionAccount()
    {
        $result = self::modelAccount()->getCollection();
        return $result;
    }

    /**
     * @return Praxigento_Bonus_Resource_Own_Balance_Collection
     */
    public function collectionBalance()
    {
        $result = self::modelBalance()->getCollection();
        return $result;
    }

    /**
     * @return Praxigento_Bonus_Model_Own_Account
     */
    public function modelAccount()
    {
        $result = Mage::getModel('prxgt_bonus_model/account');
        return $result;
    }

    /**
     * @return Praxigento_Bonus_Model_Own_Balance
     */
    public function modelBalance()
    {
        $result = Mage::getModel('prxgt_bonus_model/balance');
        return $result;
    }

    /**
     * @return Praxigento_Bonus_Resource_Own_Operation_Collection
     */
    public function collectionOperation()
    {
        $result = self::modelOperation()->getCollection();
        return $result;
    }

    /**
     * @return Praxigento_Bonus_Model_Own_Operation
     */
    public function modelOperation()
    {
        $result = Mage::getModel('prxgt_bonus_model/operation');
        return $result;
    }

    /**
     * @return Praxigento_Bonus_Resource_Own_Transaction_Collection
     */
    public function collectionTransaction()
    {
        $result = self::modelTransaction()->getCollection();
        return $result;
    }

    /**
     * @return Praxigento_Bonus_Model_Own_Transaction
     */
    public function modelTransaction()
    {
        $result = Mage::getModel('prxgt_bonus_model/transaction');
        return $result;
    }

    /**
     * @return Praxigento_Bonus_Resource_Own_Type_Asset_Collection
     */
    public function collectionTypeAsset()
    {
        $result = self::modelTypeAsset()->getCollection();
        return $result;
    }

    /**
     * @return Praxigento_Bonus_Model_Own_Type_Asset
     */
    public function modelTypeAsset()
    {
        $result = Mage::getModel('prxgt_bonus_model/type_asset');
        return $result;
    }

    /**
     * @return Praxigento_Bonus_Resource_Own_Type_Calc_Collection
     */
    public function collectionTypeCalc()
    {
        $result = self::modelTypeCalc()->getCollection();
        return $result;
    }

    /**
     * @return Praxigento_Bonus_Model_Own_Type_Calc
     */
    public function modelTypeCalc()
    {
        $result = Mage::getModel('prxgt_bonus_model/type_calc');
        return $result;
    }

    /**
     * @return Praxigento_Bonus_Resource_Own_Type_Oper_Collection
     */
    public function collectionTypeOper()
    {
        $result = self::modelTypeOper()->getCollection();
        return $result;
    }

    /**
     * @return Praxigento_Bonus_Model_Own_Type_Oper
     */
    public function modelTypeOper()
    {
        $result = Mage::getModel('prxgt_bonus_model/type_oper');
        return $result;
    }

    /**
     * @return Praxigento_Bonus_Resource_Own_Type_Period_Collection
     */
    public function collectionTypePeriod()
    {
        $result = self::modelTypePeriod()->getCollection();
        return $result;
    }

    /**
     * @return Praxigento_Bonus_Model_Own_Type_Period
     */
    public function modelTypePeriod()
    {
        $result = Mage::getModel('prxgt_bonus_model/type_period');
        return $result;
    }

    /**
     * @return Praxigento_Bonus_Service_Calculation_Call
     */
    public function serviceCalculation()
    {
        $result = Mage::getModel('prxgt_bonus_service/calculation_call');
        return $result;
    }

    /**
     * @return Praxigento_Bonus_Service_Operations_Call
     */
    public function serviceOperations()
    {
        $result = Mage::getModel('prxgt_bonus_service/operations_call');
        return $result;
    }

    /**
     * @return Praxigento_Bonus_Service_Period_Call
     */
    public function servicePeriod()
    {
        $result = Mage::getModel('prxgt_bonus_service/period_call');
        return $result;
    }


}