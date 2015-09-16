<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;
use Praxigento_Bonus_Model_Own_Log_Downline as LogDownline;
use Praxigento_Bonus_Model_Own_Snap_Downline as SnapDownline;
use Praxigento_Bonus_Service_Snapshot_Request_ComposeDownlineSnapshot as ComposeDownlineSnapshotRequest;
use Praxigento_Bonus_Service_Snapshot_Request_GetDownlineSnapshotEntry as GetDownlineSnapshotEntryRequest;
use Praxigento_Bonus_Service_Snapshot_Request_ValidateDownlineSnapshot as ValidateDownlineSnapshotRequest;
use Praxigento_Bonus_Service_Snapshot_Response_ComposeDownlineSnapshot as ComposeDownlineSnapshotResponse;
use Praxigento_Bonus_Service_Snapshot_Response_GetDownlineSnapshotEntry as GetDownlineSnapshotEntryResponse;
use Praxigento_Bonus_Service_Snapshot_Response_ValidateDownlineSnapshot as ValidateDownlineSnapshotResponse;

/**
 *
 * Compose snapshots for downline tree, etc.
 *
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Service_Snapshot_Call
    extends Praxigento_Bonus_Service_Base_Call {

    /** @var mixed Praxigento_Bonus_Service_Snapshot_Hndl_Db */
    private $_hndlDb;
    /** @var mixed Praxigento_Bonus_Service_Snapshot_Hndl_Downline */
    private $_hndlDownline;

    /**
     * Praxigento_Bonus_Service_Snapshot_Call constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->_hndlDb = Config::get()->model(Config::CFG_SERVICE . '/snapshot_hndl_db');
        $this->_hndlDownline = Config::get()->model(Config::CFG_SERVICE . '/snapshot_hndl_downline');
    }

    /**
     * @param Praxigento_Bonus_Service_Snapshot_Request_ComposeDownlineSnapshot $req
     *
     * @return Praxigento_Bonus_Service_Snapshot_Response_ComposeDownlineSnapshot
     */
    public function composeDownlineSnapshot(ComposeDownlineSnapshotRequest $req) {
        /** @var  $result ComposeDownlineSnapshotResponse */
        $result = Config::get()->model(Config::CFG_SERVICE . '/snapshot_response_composeDownlineSnapshot');
        $periodValue = $req->getPeriodValue();
        $periodValueDaily = $this->_helperPeriod->calcPeriodSmallest($periodValue);
        /* check if there is data for given period in downline snapshots*/
        $periodExists = $this->_hndlDb->isThereDownlinesSnapForPeriod($periodValueDaily);
        if(is_null($periodExists)) {
            $this->_log->debug("There is no downline snapshot data for period '$periodValue/$periodValueDaily'");
            $maxExistingPeriod = $this->_hndlDb->getLatestDownlineSnapBeforePeriod($periodValueDaily);
            /* array of the aggregated previous snap & log data */
            $arrAggregated = array();
            $from = null;
            $to = $this->_helperPeriod->calcPeriodTsTo($periodValueDaily, Config::PERIOD_DAY);
            if(is_null($maxExistingPeriod)) {
                $this->_log->debug("There is no downline snapshot data for periods before '$periodValue/$periodValueDaily'. Getting up date for the first downline log record.");
                $from = $this->_hndlDb->getFirstDownlineLogBeforePeriod($periodValue);
                $this->_log->debug("First downline log record is at '$from'");
            } else {
                /* load snapshot for existing period */
                $latestSnap = $this->_hndlDb->getDownlineSnapForPeriod($maxExistingPeriod);
                foreach($latestSnap as $one) {
                    $arrAggregated[ $one[ SnapDownline::ATTR_CUSTOMER_ID ] ] = $one[ SnapDownline::ATTR_PARENT_ID ];
                }
                $from = $this->_helperPeriod->calcPeriodTsNextFrom($maxExistingPeriod, Config::PERIOD_DAY);
            }
            /* load logs from the latest snapshot (or from beginning) and process it to get final state for period */
            $logs = $this->_hndlDb->getDownlineLogs($from, $to);
            foreach($logs as $one) {
                $ownId = $one[ LogDownline::ATTR_CUSTOMER_ID ];
                $parentId = $one[ LogDownline::ATTR_PARENT_ID ];
                $arrAggregated[ $ownId ] = $parentId;
            }
            try {
                $snapshot = $this->_hndlDownline->transformIdsToSnapItems($arrAggregated, $periodValueDaily);
                $this->_hndlDb->saveDownlineSnaps($snapshot);
                $result->setPeriodValue($periodValue);
                $result->setErrorCode(ComposeDownlineSnapshotResponse::ERR_NO_ERROR);
            } catch(Exception $e) {
                $msg = "Cannot save snapshot data for period '$periodValue/$periodValueDaily'. Reason: "
                       . $e->getMessage();
                $this->_log->debug($msg);
            }
        } else {
            $this->_log->debug("There is downline snapshot data for period '$periodValue/$periodExists'.");
            $result->setPeriodValue($periodExists);
            $result->setErrorCode(ComposeDownlineSnapshotResponse::ERR_NO_ERROR);
        }
        return $result;
    }

    /**
     * @param Praxigento_Bonus_Service_Snapshot_Request_GetDownlineSnapshotEntry $req
     *
     * @return Praxigento_Bonus_Service_Snapshot_Response_GetDownlineSnapshotEntry
     */
    public function getDownlineSnapshotEntry(GetDownlineSnapshotEntryRequest $req) {
        /** @var  $result GetDownlineSnapshotEntryResponse */
        $result = Config::get()->model(Config::CFG_SERVICE . '/snapshot_response_getDownlineSnapshotEntry');
        $custId = $req->getCustomerId();
        $periodValue = $req->getPeriodValue();
        $periodExact = $this->_helperPeriod->calcPeriodSmallest($periodValue);
        /** @var  $rsrc Mage_Core_Model_Resource */
        $rsrc = Config::get()->singleton('core/resource');
        /** @var  $conn Varien_Db_Adapter_Interface */
        $conn = $rsrc->getConnection('core_write');
        $tbl = $rsrc->getTableName(Config::CFG_MODEL . '/' . Config::ENTITY_SNAP_DOWNLINE);
        $colCustId = SnapDownline::ATTR_CUSTOMER_ID;
        $colPeriod = SnapDownline::ATTR_PERIOD;
        $bind = array(
            'cust_id' => $custId,
            'period'  => $periodExact
        );
        /* exact match */
        $sql = "SELECT * FROM $tbl WHERE $colCustId=:cust_id AND $colPeriod=:period";
        $entry = $conn->fetchAll($sql, $bind);
        if(!is_array($entry) || !count($entry)) {
            $sql = "SELECT * FROM $tbl WHERE $colCustId=:cust_id AND $colPeriod<:period ORDER BY $colPeriod DESC";
            $entry = $conn->fetchRow($sql, $bind);
            if(!is_array($entry) || !count($entry)) {
                $sql = "SELECT * FROM $tbl WHERE $colCustId=:cust_id AND $colPeriod>:period ORDER BY $colPeriod ASC";
                $entry = $conn->fetchRow($sql, $bind);
            }
        }
        if(is_array($entry)) { // fetchAll
            if(isset($entry[0])) { // fetchRow
                $entry = reset($entry);
            }
            $result->setCustomerId($entry[ SnapDownline::ATTR_CUSTOMER_ID ]);
            $result->setDepth($entry[ SnapDownline::ATTR_DEPTH ]);
            $result->setParentId($entry[ SnapDownline::ATTR_PARENT_ID ]);
            $result->setPath($entry[ SnapDownline::ATTR_PATH ]);
            $result->setPeriodExact($entry[ SnapDownline::ATTR_PERIOD ]);
            $result->setPeriodRequested($periodValue);
            $result->setErrorCode(GetDownlineSnapshotEntryResponse::ERR_NO_ERROR);
        } else {
            $result->setErrorCode(GetDownlineSnapshotEntryResponse::ERR_SNAP_IS_NOT_FOUND);
            $msg = "Snapshot entry is not found for customer #$custId and period '$periodValue/$periodExact'.";
            $result->setErrorMessage($msg);
            $this->_log->warn($msg);
        }
        return $result;
    }

    /**
     * @param Praxigento_Bonus_Service_Snapshot_Request_ValidateDownlineSnapshot $req
     *
     * @return Praxigento_Bonus_Service_Snapshot_Response_ValidateDownlineSnapshot
     */
    public function validateDownlineSnapshot(ValidateDownlineSnapshotRequest $req) {
        /** @var  $result ValidateDownlineSnapshotResponse */
        $result = Config::get()->model(Config::CFG_SERVICE . '/snapshot_response_validateDownlineSnapshot');

        $periodValue = $this->_helperPeriod->calcPeriodSmallest($req->getPeriodValue());
        $entries = $this->_hndlDb->getDownlineSnapForPeriod($periodValue, true, $asDepth = 'depth');
        $allByCustomerId = array();
        $allOrphans = array();
        $allWrongPaths = array();
        $maxDepth = 0;
        $totalCustomers = count($entries);
        $totalRoots = 0;
        foreach($entries as $one) {
            $custId = $one[ SnapDownline::ATTR_CUSTOMER_ID ];
            $parentId = $one[ SnapDownline::ATTR_PARENT_ID ];
            /* register customers */
            $allByCustomerId[ $custId ] = $one;
            /* validate parents and save customers without parents */
            if(!isset($allByCustomerId[ $parentId ])) {
                $allOrphans[ $custId ] = $one;
            } else {
                if($custId == $parentId) {
                    /* this is root node */
                    $totalRoots++;
                } else {
                    $path = $one[ SnapDownline::ATTR_PATH ];
                    $parent = $allByCustomerId[ $parentId ];
                    $pathParent = $parent[ SnapDownline::ATTR_PATH ];
                    /* validate paths and save customers with wrong paths */
                    if($path != $pathParent . $parentId . Config::FORMAT_PATH_SEPARATOR) {
                        $allWrongPaths[ $custId ] = $one;
                    }
                }
            }
            /* save max depth */
            $max = (int)$one[ $asDepth ];
            if($max > $maxDepth) {
                $maxDepth = $max;
            }
        }
        unset($entries);
        $result->setAllOrphans($allOrphans);
        $result->setAllWrongPaths($allWrongPaths);
        $result->setMaxDepth($maxDepth);
        $result->setTotalCustomers($totalCustomers);
        $result->setTotalOrphans(count($allOrphans));
        $result->setTotalRoots($totalRoots);
        $result->setTotalWrongPaths(count($allWrongPaths));
        $result->setErrorCode(ValidateDownlineSnapshotResponse::ERR_NO_ERROR);
        return $result;
    }

    /**
     * Request model to be populated.
     *
     * @return Praxigento_Bonus_Service_Snapshot_Request_ComposeDownlineSnapshot
     */
    public function requestComposeDownlineSnapshot() {
        $result = Config::get()->model(Config::CFG_SERVICE . '/snapshot_request_composeDownlineSnapshot');
        return $result;
    }

    /**
     * @return Praxigento_Bonus_Service_Snapshot_Request_GetDownlineSnapshotEntry
     */
    public function requestGetDownlineSnapshotEntry() {
        /** @var  $result GetDownlineSnapshotEntryRequest */
        $result = Config::get()->model(Config::CFG_SERVICE . '/snapshot_request_getDownlineSnapshotEntry');
        return $result;
    }

    /**
     * Request model to be populated.
     *
     * @return Praxigento_Bonus_Service_Snapshot_Request_ValidateDownlineSnapshot
     */
    public function requestValidateDownlineSnapshot() {
        $result = Config::get()->model(Config::CFG_SERVICE . '/snapshot_request_validateDownlineSnapshot');
        return $result;
    }
}

class Praxigento_Bonus_Service_Snapshot_Call_Tree_Node {
    public $customerId;
    public $depth;
    public $ndx;
    public $parentId;
    public $path;
    public $subtree = array();
}