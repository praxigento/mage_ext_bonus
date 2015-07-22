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
 */
class Praxigento_Bonus_Model_Own_Snap_Downline_Hist extends Mage_Core_Model_Abstract
{
    const ATTR_ID = 'id';

    protected function _construct()
    {
        $this->_init(Config::CFG_MODEL . '/' . Config::ENTITY_SNAP_DOWNLINE_HIST);
    }

}