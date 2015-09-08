<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;
use Praxigento_Bonus_Model_Own_Snap_Downline as SnapDownline;

/**
 * Database routines handler for the service.
 *
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Service_Snapshot_Hndl_Db {

    public function isThereDownlinesSnapForPeriod($periodValue) {
        $result = null;
        /** @var  $rsrc Mage_Core_Model_Resource */
        $rsrc      = Config::get()->singleton('core/resource');
        $tbl       = $rsrc->getTableName(Config::CFG_MODEL . '/' . Config::ENTITY_SNAP_DOWNLINE);
        $conn      = $rsrc->getConnection('core_write');
        $colPeriod = SnapDownline::ATTR_PERIOD;
        $rs        = $conn->fetchOne("SELECT COUNT(*) FROM $tbl WHERE $colPeriod=:period", array( 'period' => $periodValue ));
        if($rs > 0) {
            $result = $periodValue;
        } else {
            /* we should look up for more detailed period */
            /** @var  $hlp */
            $hlp      = Config::get()->helperPeriod();
            $smallest = $hlp->calcPeriodSmallest($periodValue);
            $rs       = $conn->fetchOne("SELECT COUNT(*) FROM $tbl WHERE $colPeriod=:period", array( 'period' => $smallest ));
            if($rs > 0) {
                $result = $smallest;
            }
        }
        return $result;
    }

}