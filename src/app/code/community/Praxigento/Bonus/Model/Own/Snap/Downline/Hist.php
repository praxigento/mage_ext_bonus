<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;

/**
 * Historical downline tree snapshots.
 *
 * User: Alex Gusev <alex@flancer64.com>
 *
 * @method string getPeriod()
 * @method null setPeriod(string $val)
 *
 */
class Praxigento_Bonus_Model_Own_Snap_Downline_Hist extends Praxigento_Bonus_Model_Own_Snap_Downline
{
    const ATTR_PERIOD = 'period';

    protected function _construct()
    {
        $this->_init(Config::CFG_MODEL . '/' . Config::ENTITY_SNAP_DOWNLINE_HIST);
    }

}