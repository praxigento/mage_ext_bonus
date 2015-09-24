<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;
use Praxigento_Bonus_Model_Own_Log_Downline as LogDownline;
use Praxigento_Bonus_Model_Own_Snap_Downline as SnapDownline;
use Praxigento_Bonus_Service_Snapshot_Request_ChangeUpline as ChangeUplineRequest;
use Praxigento_Bonus_Service_Snapshot_Request_ComposeDownlineSnapshot as ComposeDownlineSnapshotRequest;
use Praxigento_Bonus_Service_Snapshot_Request_GetDownlineSnapshotEntry as GetDownlineSnapshotEntryRequest;
use Praxigento_Bonus_Service_Snapshot_Request_ValidateDownlineSnapshot as ValidateDownlineSnapshotRequest;
use Praxigento_Bonus_Service_Snapshot_Response_ChangeUpline as ChangeUplineResponse;
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


    /**
     * @param Praxigento_Bonus_Service_Snapshot_Request_ComposeDownlineSnapshot $req
     *
     * @return Praxigento_Bonus_Service_Snapshot_Response_ComposeDownlineSnapshot
     */
    public function composeDownlineSnapshot(ComposeDownlineSnapshotRequest $req) {
        /** @var  $cfg Config */
        $cfg = Config::get();
        /** @var  $hlpPeriod Praxigento_Bonus_Helper_Period */
        $hlpPeriod = $cfg->helperPeriod();
        /** @var  $hndlDb  Praxigento_Bonus_Service_Snapshot_Hndl_Db */
        $hndlDb = $cfg->singleton(Config::CFG_SERVICE . '/snapshot_hndl_db');
        /** @var  $hndlDownline Praxigento_Bonus_Service_Snapshot_Hndl_Downline */
        $hndlDownline = $cfg->singleton(Config::CFG_SERVICE . '/snapshot_hndl_downline');
        /** @var  $result ComposeDownlineSnapshotResponse */
        $result = $cfg->model(Config::CFG_SERVICE . '/snapshot_response_composeDownlineSnapshot');
        /* parse request parameters and calculate exact period value (daily basis)*/
        $periodValue = $req->getPeriodValue();
        $periodValueDaily = $hlpPeriod->calcPeriodSmallest($periodValue);
        /* check if there is data for given period in downline snapshots*/
        $periodExists = $hndlDb->isThereDownlinesSnapForPeriod($periodValueDaily);
        if(is_null($periodExists)) {
            $this->_log->debug("There is no downline snapshot data for period '$periodValue/$periodValueDaily'");
            $maxExistingPeriod = $hndlDb->getLatestDownlineSnapBeforePeriod($periodValueDaily);
            /* array of the aggregated previous snap & log data */
            $arrAggregated = array();
            $from = null;
            $to = $hlpPeriod->calcPeriodTsTo($periodValueDaily, Config::PERIOD_DAY);
            if(is_null($maxExistingPeriod)) {
                $this->_log->debug("There is no downline snapshot data for periods before '$periodValue/$periodValueDaily'. Getting up date for the first downline log record.");
                $from = $hndlDb->getFirstDownlineLogBeforePeriod($periodValue);
                $this->_log->debug("First downline log record is at '$from'");
            } else {
                /* load snapshot for existing period */
                $latestSnap = $hndlDb->getDownlineSnapForPeriod($maxExistingPeriod);
                foreach($latestSnap as $one) {
                    $arrAggregated[ $one[ SnapDownline::ATTR_CUSTOMER_ID ] ] = $one[ SnapDownline::ATTR_PARENT_ID ];
                }
                $from = $hlpPeriod->calcPeriodTsNextFrom($maxExistingPeriod, Config::PERIOD_DAY);
            }
            /* load logs from the latest snapshot (or from beginning) and process it to get final state for period */
            $logs = $hndlDb->getDownlineLogs($from, $to);
            foreach($logs as $one) {
                $ownId = $one[ LogDownline::ATTR_CUSTOMER_ID ];
                $parentId = $one[ LogDownline::ATTR_PARENT_ID ];
                $arrAggregated[ $ownId ] = $parentId;
            }
            try {
                $snapshot = $hndlDownline->transformIdsToSnapItems($arrAggregated, $periodValueDaily);
                $hndlDb->saveDownlineSnaps($snapshot);
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
     * @param Praxigento_Bonus_Service_Snapshot_Request_ChangeUpline $req
     *
     * @return Praxigento_Bonus_Service_Snapshot_Response_ChangeUpline
     */
    public function changeUpline(ChangeUplineRequest $req) {
        /** @var  $cfg Config */
        $cfg = Config::get();
        $hndlDb = $cfg->singleton(Config::CFG_SERVICE . '/snapshot_hndl_db');
        /** @var  $result ChangeUplineResponse */
        $result = $cfg->model(Config::CFG_SERVICE . '/snapshot_response_changeUpline');
        $custId = $req->getCustomerId();
        $newParentId = $req->getNewUplineId();
        $this->_log->debug("Change parent for the customer $custId (new parent: $newParentId).");
        if($custId == $newParentId) {
            $result->setErrorCode(ChangeUplineResponse::ERR_PARENT_IS_THE_CUSTOMER);
            $this->_log->error("Cannot change parent to itself.");
        } else {
            /* get customer current data from downline snapshot */
            $entryCust = $hndlDb->getDownlineSnapEntry($custId);
            if($entryCust->getParentId() == $newParentId) {
                $result->setErrorCode(ChangeUplineResponse::ERR_PARENT_ALREADY_SET);
                $this->_log->error("This parent is already set to the customer.");
            } else {
                /* get parent current data from downline snapshot */
                $entryNewParent = $hndlDb->getDownlineSnapEntry($newParentId);
                $pathNewParent = $entryNewParent->getPath();
                $pathIds = explode(Config::MPS, $pathNewParent);
                if(in_array($custId, $pathIds)) {
                    $result->setErrorCode(ChangeUplineResponse::ERR_PARENT_IS_FROM_DOWNLINE);
                    $this->_log->error("Cannot set parent from own downline (new parent path: $pathNewParent).");
                } else {
                    /* validation is complete, do the job */
                    $conn = $cfg->connectionWrite();
                    $conn->beginTransaction();
                    try {
                        /* insert new entry to the log */
                        $tblLogDwnl = $cfg->tableName(Config::ENTITY_LOG_DOWNLINE);
                        $bind = array(
                            LogDownline::ATTR_CUSTOMER_ID => $custId,
                            LogDownline::ATTR_PARENT_ID   => $newParentId
                        );
                        $conn->insert($tblLogDwnl, $bind);
                        /* update parent for the customer */
                        $pathNew = $pathNewParent . $newParentId . Config::MPS;
                        $depthNew = $entryNewParent->getDepth() + 1;
                        $hndlDb->updateDownlineSnapParent(
                            $custId,
                            Config::PERIOD_KEY_NOW,
                            $newParentId,
                            $pathNew,
                            $depthNew
                        );
                        /* select all downline entries for the customer and update all of them */
                        $pathChildKey = $entryCust->getPath() . $entryCust->getCustomerId() . Config::MPS;
                        $pathChildReplace = $pathNew . $custId . Config::MPS;
                        $depthChildDelta = $depthNew - $entryCust->getDepth();
                        $hndlDb->updateDownlineSnapChildren(
                            $pathChildKey,
                            $pathChildReplace,
                            $depthChildDelta,
                            Config::PERIOD_KEY_NOW
                        );
                        $conn->commit();
                        $result->setErrorCode(ChangeUplineResponse::ERR_NO_ERROR);
                    } catch(Exception $e) {
                        $conn->rollBack();
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @param Praxigento_Bonus_Service_Snapshot_Request_GetDownlineSnapshotEntry $req
     *
     * @return Praxigento_Bonus_Service_Snapshot_Response_GetDownlineSnapshotEntry
     */
    public function getDownlineSnapshotEntry(GetDownlineSnapshotEntryRequest $req) {
        /** @var  $cfg Config */
        $cfg = Config::get();
        $hlpPeriod = $cfg->helperPeriod();
        /** @var  $result GetDownlineSnapshotEntryResponse */
        $result = Config::get()->model(Config::CFG_SERVICE . '/snapshot_response_getDownlineSnapshotEntry');
        $custId = $req->getCustomerId();
        $periodValue = $req->getPeriodValue();
        $periodExact = $hlpPeriod->calcPeriodSmallest($periodValue);
        /** @var  $rsrc Mage_Core_Model_Resource */
        $rsrc = Config::get()->singleton('core/resource');
        /** @var  $conn Varien_Db_Adapter_Interface */
        $conn = $rsrc->getConnection('core_write');
        $tbl = $rsrc->getTableName(Config::ENTITY_SNAP_DOWNLINE);
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
        /** @var  $cfg Config */
        $cfg = Config::get();
        $hlpPeriod = $cfg->helperPeriod();
        $hndlDb = $cfg->singleton(Config::CFG_SERVICE . '/snapshot_hndl_db');
        /** @var  $result ValidateDownlineSnapshotResponse */
        $result = Config::get()->model(Config::CFG_SERVICE . '/snapshot_response_validateDownlineSnapshot');

        $periodValue = $hlpPeriod->calcPeriodSmallest($req->getPeriodValue());
        $entries = $hndlDb->getDownlineSnapForPeriod($periodValue);
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
            $max = (int)$one[ SnapDownline::ATTR_DEPTH ];
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
        /** @var  $result ComposeDownlineSnapshotRequest */
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
     * @return Praxigento_Bonus_Service_Snapshot_Request_ChangeUpline
     */
    public function requestChangeUpline() {
        /** @var  $result ChangeUplineRequest */
        $result = Config::get()->model(Config::CFG_SERVICE . '/snapshot_request_changeUpline');
        return $result;
    }

    /**
     * Request model to be populated.
     *
     * @return Praxigento_Bonus_Service_Snapshot_Request_ValidateDownlineSnapshot
     */
    public function requestValidateDownlineSnapshot() {
        /** @var  $result ValidateDownlineSnapshotRequest */
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