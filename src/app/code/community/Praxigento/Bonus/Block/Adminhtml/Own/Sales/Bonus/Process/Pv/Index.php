<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Block_Adminhtml_Own_Sales_Bonus_Process_Pv_Index
    extends Praxigento_Bonus_Block_Adminhtml_Own_Base {

    public function uiPeriodType() {
        $hlp = Config::get()->helper();
        $type = $hlp->cfgPersonalBonusPeriod();
        if($type == Config::PERIOD_WEEK) {
            $weekday = $hlp->cfgPersonalBonusWeekLastDay();
            $type = $type . " ($weekday)";
        }
        echo $type;
    }

    public function uiPeriodCurrent() {
        $calc = Config::get()->servicePeriod();
        $resp = $calc->getPeriodForPvWriteOff();
        echo $resp->getPeriodValue();
    }

}