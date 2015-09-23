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
     * Return downline snap data for the given $periodValue and sort it by path depth (if $sortByDepth is 'true',
     * $asDepth is used as alias for the computed value depth).
     *
     * @param            $periodValue
     * @param bool|false $sortByDepth
     * @param string     $asDepth
     *
     * @return array
     */
    public function getDownlineSnapForPeriod($periodValue, $sortByDepth = false, $asDepth = 'depth') {
        /* convert period to the daily form (201506 => 20150630) */
        $hlp = Config::get()->helperPeriod();
        $smallest = $hlp->calcPeriodSmallest($periodValue);
        /** @var  $rsrc Mage_Core_Model_Resource */
        $rsrc = Config::get()->singleton('core/resource');
        $tbl = $rsrc->getTableName(Config::CFG_MODEL . '/' . Config::ENTITY_SNAP_DOWNLINE);
        $conn = $rsrc->getConnection('core_write');
        $colPeriod = SnapDownline::ATTR_PERIOD;
        $colPath = SnapDownline::ATTR_PATH;
        $ps = Config::FORMAT_PATH_SEPARATOR;
        $sql = "SELECT * FROM $tbl WHERE $colPeriod=:period";
        if($sortByDepth) {
            $sql = "SELECT *, (LENGTH($colPath) - LENGTH(REPLACE($colPath, \"$ps\", \"\"))) as $asDepth" .
                   "  FROM $tbl WHERE $colPeriod=:period ORDER BY $asDepth ASC";
        }
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
        /** @var  $result Praxigento_Bonus_Model_Own_Snap_Downline */
        $result = Config::get()->modelSnapDownline();
        /** @var  $rsrc Mage_Core_Model_Resource */
        $rsrc = Config::get()->singleton('core/resource');
        /** @var  $conn Varien_Db_Adapter_Interface */
        $conn = $rsrc->getConnection('core_write');
        /* prepare table aliases and models */
        $asSnap = 'snap';
        $eSnap = Config::CFG_MODEL . '/' . Config::ENTITY_SNAP_DOWNLINE;
        $tblSnap = Config::get()->tableName($eSnap, $asSnap);
        /** @var  $query Varien_Db_Select */
        $query = $conn->select();
        $cols = '*';
        $query->from($tblSnap, $cols);
        $query->where($asSnap . '.' . SnapDownline::ATTR_CUSTOMER_ID . '=:id');
        $query->where($asSnap . '.' . SnapDownline::ATTR_PERIOD . '=:period');
        $sql = (string)$query;
        $data = $conn->fetchRow($query, array( 'id' => $custId, 'period' => $periodValue ));
        $result->setData($data);
        return $result;
    }

    /**
     * @param $periodValue
     *
     * @return string|null
     */
    public function getFirstDownlineLogBeforePeriod($periodValue) {
        /** @var  $cfg Config */
        $cfg = Config::get();
        /* convert period to the daily form (201506 => 20150630) */
        $hlp = $cfg->helperPeriod();
        $periodDaily = $hlp->calcPeriodSmallest($periodValue);
        $hlpPeriod = $cfg->helperPeriod();
        $to = $hlpPeriod->calcPeriodTsTo($periodDaily, Config::PERIOD_DAY);
        $tbl = $cfg->tableName(Config::CFG_MODEL . '/' . Config::ENTITY_LOG_DOWNLINE);
        $conn = $cfg->connectionWrite();
        $colChanged = LogDownline::ATTR_DATE_CHANGED;
        $sql = "SELECT MIN(date_changed) FROM $tbl WHERE $colChanged<=:changed";
        $result = $conn->fetchOne(
            $sql,
            array( 'changed' => $to )
        );
        return $result;
    }

    public function updateDownlineSnapParent($custId, $period, $parentIdNew, $pathNew, $depthNew) {
        $conn = Config::get()->connectionWrite();
        $tblSnapDwnl = Config::get()->tableName(Config::CFG_MODEL . '/' . Config::ENTITY_SNAP_DOWNLINE);
        $bind = array(
            SnapDownline::ATTR_PARENT_ID => $parentIdNew,
            SnapDownline::ATTR_PATH      => $pathNew,
            SnapDownline::ATTR_DEPTH     => $depthNew
        );
        $whereCust = $conn->quoteInto(SnapDownline::ATTR_CUSTOMER_ID . '=?', $custId);
        $wherePeriod = $conn->quoteInto(SnapDownline::ATTR_PERIOD . '=?', $period);
        $where = "$whereCust AND $wherePeriod";
        $conn->update($tblSnapDwnl, $bind, $where);
    }

    /**
     * Update paths for all children in downline when customer parent is changed.
     *
     * @param $pathOld
     * @param $pathNew
     * @param $depthDelta
     */
    public function updateDownlineSnapChildren($pathOld, $pathNew, $depthDelta) {
        /** @var  $cfg Config */
        $cfg = Config::get();
        $conn = $cfg->connectionWrite();
        $query = $conn->select();
        $cols = '*';
        $tblSnapDwnl = $cfg->tableName(Config::CFG_MODEL . '/' . Config::ENTITY_SNAP_DOWNLINE);
        $query->from($tblSnapDwnl, $cols);
        $query->where(SnapDownline::ATTR_PATH . ' LIKE :path');
        $sql = (string)$query;
        $all = $conn->fetchAll($query, array( 'path' => $pathOld . '%' ));
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
            $conn->update($tblSnapDwnl, $bind, $where);
        }
    }

    public function saveDownlineSnaps($snapshot) {
        /** @var  $rsrc Mage_Core_Model_Resource */
        $rsrc = Config::get()->singleton('core/resource');
        /** @var  $conn Varien_Db_Adapter_Interface */
        $conn = $rsrc->getConnection('core_write');
        $conn->beginTransaction();
        try {
            $tblSnapDownline = $rsrc->getTableName(Config::CFG_MODEL . '/' . Config::ENTITY_SNAP_DOWNLINE);
            $conn->insertMultiple($tblSnapDownline, $snapshot);
            $conn->commit();
        } catch(Exception $e) {
            $conn->rollBack();
            Mage::throwException($e->getMessage());

        }
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