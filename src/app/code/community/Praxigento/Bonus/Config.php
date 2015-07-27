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
     * Available bonuses codes.
     */

    const BONUS_COURTESY = 'cb';
    const BONUS_GROUP = 'gv';
    const BONUS_INFINITY = 'ib';
    const BONUS_OVERRIDE = 'ob';
    const BONUS_PERSONAL = 'pv';
    const BONUS_RETAIL = 'retail';
    const BONUS_TEAM = 'tv';

    /**
     * 'config.xml' related constants.
     */

    const CFG_BLOCK = 'prxgt_bonus_block';
    const CFG_HELPER = 'prxgt_bonus_helper';
    const CFG_MODEL = 'prxgt_bonus_model';

    /**
     * Entities in config.xml:/config/global/models/prxgt_bonus_resource/entities
     */

    const ENTITY_CFG_PERSONAL = 'cfg_personal';
    const ENTITY_CORE_TYPE = 'core_type';
    const ENTITY_DETAILS_RETAIL = 'details_retail';
    const ENTITY_LOG_ACCOUNT = 'log_account';
    const ENTITY_LOG_BONUS = 'log_bonus';
    const ENTITY_LOG_DOWNLINE = 'log_downline';
    const ENTITY_LOG_ORDER = 'log_order';
    const ENTITY_LOG_PAYOUT = 'log_payout';
    const ENTITY_SNAP_BONUS = 'snap_bonus';
    const ENTITY_SNAP_BONUS_HIST = 'snap_bonus_hist';
    const ENTITY_SNAP_DOWNLINE = 'snap_downline';
    const ENTITY_SNAP_DOWNLINE_HIST = 'snap_downline_hist';
}