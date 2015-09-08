<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;
use Praxigento_Bonus_Model_Own_Log_Downline as LogDownline;
use Praxigento_Bonus_Model_Own_Snap_Downline as SnapDownline;

/**
 * Database routines handler for the service.
 *
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Service_Snapshot_Hndl_Db {

    /**
     * Lookup existing snapshot period for given period (for example, given period '201506', existing - '20150630')
     *
     * @param $periodValue
     *
     * @return null|string
     */
    public function isThereDownlinesSnapForPeriod($periodValue) {
        $result = null;
        /** @var  $rsrc Mage_Core_Model_Resource */
        $rsrc = Config::get()->singleton('core/resource');
        $tbl = $rsrc->getTableName(Config::CFG_MODEL . '/' . Config::ENTITY_SNAP_DOWNLINE);
        $conn = $rsrc->getConnection('core_write');
        $colPeriod = SnapDownline::ATTR_PERIOD;
        $rs = $conn->fetchOne("SELECT COUNT(*) FROM $tbl WHERE $colPeriod=:period", array( 'period' => $periodValue ));
        if($rs > 0) {
            $result = $periodValue;
        } else {
            /* we should look up for more detailed period */
            /** @var  $hlp */
            $hlp = Config::get()->helperPeriod();
            $smallest = $hlp->calcPeriodSmallest($periodValue);
            $rs = $conn->fetchOne("SELECT COUNT(*) FROM $tbl WHERE $colPeriod=:period", array( 'period' => $smallest ));
            if($rs > 0) {
                $result = $smallest;
            }
        }
        return $result;
    }

    /**
     * Return the latest existing snap period less then given $periodValue or 'null' in case of such period does not
     * exist.
     *
     * @param $periodValue daily period (20150601)
     *
     * @return null|string
     */
    public function getLatestDownlineSnapBeforePeriod($periodValue) {
        /* convert period to the daily form (201506 => 20150630) */
        $hlp = Config::get()->helperPeriod();
        $smallest = $hlp->calcPeriodSmallest($periodValue);
        /** @var  $rsrc Mage_Core_Model_Resource */
        $rsrc = Config::get()->singleton('core/resource');
        $tbl = $rsrc->getTableName(Config::CFG_MODEL . '/' . Config::ENTITY_SNAP_DOWNLINE);
        $conn = $rsrc->getConnection('core_write');
        $colPeriod = SnapDownline::ATTR_PERIOD;
        $sql = "SELECT MAX(period) FROM $tbl WHERE $colPeriod<:period AND $colPeriod!=:now";
        $result = $conn->fetchOne(
            $sql,
            array( 'period' => $smallest, 'now' => Config::PERIOD_KEY_NOW )
        );
        return $result;
    }

    /**
     * @param $periodValue
     *
     * @return string|null
     */
    public function getFirstDownlineLogBeforePeriod($periodValue) {
        /* convert period to the daily form (201506 => 20150630) */
        $hlp = Config::get()->helperPeriod();
        $periodDaily = $hlp->calcPeriodSmallest($periodValue);
        $hlpPeriod = Config::get()->helperPeriod();
        $to = $hlpPeriod->calcPeriodToTs($periodDaily, Config::PERIOD_DAY);
        /** @var  $rsrc Mage_Core_Model_Resource */
        $rsrc = Config::get()->singleton('core/resource');
        $tbl = $rsrc->getTableName(Config::CFG_MODEL . '/' . Config::ENTITY_LOG_DOWNLINE);
        $conn = $rsrc->getConnection('core_write');
        $colChanged = LogDownline::ATTR_DATE_CHANGED;
        $sql = "SELECT MIN(date_changed) FROM $tbl WHERE $colChanged<=:changed";
        $result = $conn->fetchOne(
            $sql,
            array( 'changed' => $to )
        );
        return $result;
    }

    public function getDownlineLogs($from, $to) {
        $result = array();
        /** @var  $rsrc Mage_Core_Model_Resource */
        $rsrc = Config::get()->singleton('core/resource');
        $tbl = $rsrc->getTableName(Config::CFG_MODEL . '/' . Config::ENTITY_LOG_DOWNLINE);
        $conn = $rsrc->getConnection('core_write');
        $colChanged = LogDownline::ATTR_DATE_CHANGED;
        $sql = "SELECT * FROM $tbl WHERE $colChanged>=:from AND $colChanged<=:to ORDER BY $colChanged";
        $result = $conn->fetchAll(
            $sql,
            array( 'from' => $from, 'to' => $to )
        );
        return $result;
    }
}