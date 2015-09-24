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
        $cfg = Config::get();
        $conn = $cfg->connectionWrite();
        $tbl = $cfg->tableName(Config::ENTITY_SNAP_DOWNLINE);
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
        $cfg = Config::get();
        $hlp = Config::get()->helperPeriod();
        /* convert period to the daily form (201506 => 20150630) */
        $smallest = $hlp->calcPeriodSmallest($periodValue);
        $tbl = $cfg->tableName(Config::ENTITY_SNAP_DOWNLINE);
        $conn = $cfg->connectionWrite();
        $colPeriod = SnapDownline::ATTR_PERIOD;
        $sql = "SELECT MAX(period) FROM $tbl WHERE $colPeriod<:period AND $colPeriod!=:now";
        $result = $conn->fetchOne(
            $sql,
            array( 'period' => $smallest, 'now' => Config::PERIOD_KEY_NOW )
        );
        return $result;
    }

    /**
     * Return downline snapshot data for the given $periodValue and sort it by path depth (ascending).
     *
     * @param           $periodValue
     *
     * @return array    array of the found entries sorted by depth (asc)
     */
    public function getDownlineSnapForPeriod($periodValue) {
        $cfg = Config::get();
        $conn = $cfg->connectionWrite();
        $hlp = $cfg->helperPeriod();
        /* convert period to the daily form (201506 => 20150630) */
        $smallest = $hlp->calcPeriodSmallest($periodValue);
        $tbl = $cfg->tableName(Config::ENTITY_SNAP_DOWNLINE);
        $colPeriod = SnapDownline::ATTR_PERIOD;
        $colDepth = SnapDownline::ATTR_DEPTH;
        $sql = "SELECT * FROM $tbl WHERE $colPeriod=:period ORDER BY $colDepth ASC";
        $result = $conn->fetchAll(
            $sql,
            array( 'period' => $smallest )
        );
        return $result;
    }

    /**
     * Return downline snapshot entry for the customer and period (primary key).
     *
     * @param $custId
     * @param $periodValue
     *
     * @return Praxigento_Bonus_Model_Own_Snap_Downline
     */
    public function getDownlineSnapEntry($custId, $periodValue = Config::PERIOD_KEY_NOW) {
        $cfg = Config::get();
        $conn = $cfg->connectionWrite();
        /** @var  $result Praxigento_Bonus_Model_Own_Snap_Downline */
        $result = $cfg->modelSnapDownline();
        /* prepare table aliases and models */
        $asSnap = 'snap';
        $eSnap = Config::ENTITY_SNAP_DOWNLINE;
        $tblSnap = $cfg->tableName($eSnap, $asSnap);
        /** @var  $query Varien_Db_Select */
        $query = $conn->select();
        $cols = '*';
        $query->from($tblSnap, $cols);
        $query->where($asSnap . '.' . SnapDownline::ATTR_CUSTOMER_ID . '=:id');
        $query->where($asSnap . '.' . SnapDownline::ATTR_PERIOD . '=:period');
        $sql = (string)$query;
        $data = $conn->fetchRow($query, array( 'id' => $custId, 'period' => $periodValue ));
        if($data) {
            $result->setData($data);
        }
        return $result;
    }

    /**
     * Return the first downline log record before $periodValue (used if there are no data in the snapshot table
     * and we need to create new snapshot from the beginning).
     *
     * @param $periodValue
     *
     * @return string|null
     */
    public function getFirstDownlineLogBeforePeriod($periodValue) {
        /** @var  $cfg Config */
        $cfg = Config::get();
        $conn = $cfg->connectionWrite();
        $hlpPeriod = $cfg->helperPeriod();
        /* convert period to the daily form (201506 => 20150630) */
        $periodDaily = $hlpPeriod->calcPeriodSmallest($periodValue);
        $to = $hlpPeriod->calcPeriodTsTo($periodDaily, Config::PERIOD_DAY);
        $tbl = $cfg->tableName(Config::ENTITY_LOG_DOWNLINE);
        $colChanged = LogDownline::ATTR_DATE_CHANGED;
        $sql = "SELECT MIN($colChanged) FROM $tbl WHERE $colChanged<=:changed";
        $result = $conn->fetchOne(
            $sql,
            array( 'changed' => $to )
        );
        return $result;
    }

    /**
     * Update paths for all children in the customer downline when customer's parent is changed.
     *
     * @param $pathOld
     * @param $pathNew
     * @param $depthDelta int Value to change current depth of the items in downline (customer itself depth change).
     * @param $periodValue
     *
     * @return int Total number of updated rows
     */
    public function updateDownlineSnapChildren($pathOld, $pathNew, $depthDelta, $periodValue = Config::PERIOD_KEY_NOW) {
        $result = 0;
        /** @var  $cfg Config */
        $cfg = Config::get();
        $conn = $cfg->connectionWrite();
        /* select all downline customers by path */
        /** @var  $query 'Varien_Db_Select' */
        $query = $conn->select();
        $cols = '*';
        $tblSnapDwnl = $cfg->tableName(Config::ENTITY_SNAP_DOWNLINE);
        $query->from($tblSnapDwnl, $cols);
        $query->where(SnapDownline::ATTR_PATH . ' LIKE :path');
        $query->where(SnapDownline::ATTR_PERIOD . ' = :period');
        /* SELECT `prxgt_bonus_snap_downline`.* FROM `prxgt_bonus_snap_downline` WHERE (path LIKE :path) AND (period = :period)*/
        $sql = (string)$query;
        $all = $conn->fetchAll($query, array( 'path' => $pathOld . '%', 'period' => $periodValue ));
        foreach($all as $one) {
            $pathChildNew = str_replace($pathOld, $pathNew, $one[ SnapDownline::ATTR_PATH ]);
            $depthChildNew = $one[ SnapDownline::ATTR_DEPTH ] + $depthDelta;
            $custId = $one[ SnapDownline::ATTR_CUSTOMER_ID ];
            $period = $one[ SnapDownline::ATTR_PERIOD ];
            $bind = array(
                SnapDownline::ATTR_PATH  => $pathChildNew,
                SnapDownline::ATTR_DEPTH => $depthChildNew
            );
            /* filter by primary key */
            $whereCust = $conn->quoteInto(SnapDownline::ATTR_CUSTOMER_ID . '=?', $custId);
            $wherePeriod = $conn->quoteInto(SnapDownline::ATTR_PERIOD . '=?', $period);
            $where = "$whereCust AND $wherePeriod";
            $updated = $conn->update($tblSnapDwnl, $bind, $where);
            $result += $updated;
        }
        return $result;
    }

    /**
     * Update parent customer ID, path and depth in downline snapshots table.
     *
     * @param $custId
     * @param $period
     * @param $parentIdNew
     * @param $pathNew
     * @param $depthNew
     *
     * @return int
     */
    public function updateDownlineSnapParent($custId, $period, $parentIdNew, $pathNew, $depthNew) {
        /** @var  $cfg Config */
        $cfg = Config::get();
        $conn = $cfg->connectionWrite();
        $tblSnapDwnl = $cfg->tableName(Config::ENTITY_SNAP_DOWNLINE);
        $bind = array(
            SnapDownline::ATTR_PARENT_ID => $parentIdNew,
            SnapDownline::ATTR_PATH      => $pathNew,
            SnapDownline::ATTR_DEPTH     => $depthNew
        );
        $whereCust = $conn->quoteInto(SnapDownline::ATTR_CUSTOMER_ID . '=?', $custId);
        $wherePeriod = $conn->quoteInto(SnapDownline::ATTR_PERIOD . '=?', $period);
        $where = "$whereCust AND $wherePeriod";
        $result = $conn->update($tblSnapDwnl, $bind, $where);
        return $result;
    }

    /**
     *Save array of downline snapshot entries to DB:
     * $snap_entry= array(
     *  SnapDownline::ATTR_CUSTOMER_ID => $ownId,
     *  SnapDownline::ATTR_PARENT_ID   => $parentId,
     *  SnapDownline::ATTR_PERIOD      => $periodValue,
     *  SnapDownline::ATTR_PATH        => $path,
     *  SnapDownline::ATTR_DEPTH       => $depth
     * );
     *
     * @param $snapshot
     *
     * @return int The number of affected rows.
     */
    public function saveDownlineSnaps($snapshot) {
        $cfg = Config::get();
        $conn = $cfg->connectionWrite();
        $tblSnapDownline = $cfg->tableName(Config::ENTITY_SNAP_DOWNLINE);
        $result = $conn->insertMultiple($tblSnapDownline, $snapshot);
        return $result;
    }

    /**
     * Return downline logs for period [$from $to].
     *
     * @param $from
     * @param $to
     *
     * @return array
     */
    public function getDownlineLogs($from, $to) {
        $cfg = Config::get();
        $conn = $cfg->connectionWrite();
        $tbl = $cfg->tableName(Config::ENTITY_LOG_DOWNLINE);
        $colChanged = LogDownline::ATTR_DATE_CHANGED;
        $sql = "SELECT * FROM $tbl WHERE $colChanged>=:from AND $colChanged<=:to ORDER BY $colChanged";
        $result = $conn->fetchAll(
            $sql,
            array( 'from' => $from, 'to' => $to )
        );
        return $result;
    }
}