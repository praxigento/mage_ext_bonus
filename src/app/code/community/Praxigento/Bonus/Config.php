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

    const CALC_BONUS_COURTESY = 'cb_bonus';
    const CALC_BONUS_GROUP = 'gv_bonus';
    const CALC_BONUS_INFINITY = 'ib_bonus';
    const CALC_BONUS_OVERRIDE = 'ob_bonus';
    const CALC_BONUS_PERSONAL = 'pv_bonus';
    const CALC_BONUS_RETAIL = 'retail_bonus';
    const CALC_BONUS_TEAM = 'tv_bonus';
    const CALC_PV_WRITE_OUT = 'pv_write_out';

    /**
     * 'config.xml' related constants.
     */

    const CFG_BLOCK = 'prxgt_bonus_block';
    const CFG_HELPER = 'prxgt_bonus_helper';
    const CFG_HELPER_PERIOD = 'prxgt_bonus_helper/period';
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
    const ENTITY_LOG_BONUS = 'log_bonus';
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
    const OPER_PV_INT = 'PV_INT';
    const OPER_PV_JUMP = 'PV_JUMP';
    const OPER_PV_WRITE_OUT = 'PV_WRITE_OUT';
    const OPER_TRANS_EXT = 'TRANS_EXT';
    const OPER_TRANS_INT = 'TRANS_INT';

    /**
     * Available bonus calculation periods.
     */

    const PERIOD_DAY = 'day';
    const PERIOD_KEY_NOW = 'NOW';
    const PERIOD_MONTH = 'month';
    const PERIOD_WEEK = 'week';
    const PERIOD_YEAR = 'year';

    /**
     * Available states for bonus calculation periods.
     */

    const STATE_PERIOD_COMPLETE = 'complete';
    const STATE_PERIOD_PLACED = 'placed';
    const STATE_PERIOD_PROCESSING = 'processing';
    const STATE_PERIOD_REVERTED = 'reverted';

    /**
     * @return  \Praxigento_Bonus_Helper_Data
     */
    public static function helper()
    {
        $result = Mage::helper(self::CFG_HELPER);
        return $result;
    }

    /**
     * @return  \Praxigento_Bonus_Helper_Period
     */
    public static function helperPeriod()
    {
        $result = Mage::helper(self::CFG_HELPER_PERIOD);
        return $result;
    }
}