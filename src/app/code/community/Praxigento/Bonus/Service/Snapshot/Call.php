<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;
use Praxigento_Bonus_Model_Own_Log_Downline as LogDownline;
use Praxigento_Bonus_Service_Snapshot_Request_ComposeDownlineSnapshot as ComposeDownlineSnapshotRequest;
use Praxigento_Bonus_Service_Snapshot_Response_ComposeDownlineSnapshot as ComposeDownlineSnapshotResponse;

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

    /**
     * Praxigento_Bonus_Service_Snapshot_Call constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->_hndlDb = Config::get()->model(Config::CFG_SERVICE . '/snapshot_hndl_db');

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
            $maxExistingPeriod = $this->_hndlDb->getLatestDownlineSnapBeforePeriod();
            $snapLatest = array();
            $from = null;
            $to = $this->_helperPeriod->calcPeriodToTs($periodValueDaily, Config::PERIOD_DAY);
            if(is_null($maxExistingPeriod)) {
                $this->_log->debug("There is no downline snapshot data for periods before '$periodValue/$periodValueDaily'. Getting up date for the first downline log record.");
                $from = $this->_hndlDb->getFirstDownlineLogBeforePeriod($periodValue);
                $this->_log->debug("First downline log record is at '$from'");
            } else {
                /* load snapshot for existing period */

            }
            /* load logs from the latest snapshot (or from beginning) and process it to get result snapshot(s) */
            $logs = $this->_hndlDb->getDownlineLogs($from, $to);
            foreach($logs as $one) {
                $snapLatest[ $one[ LogDownline::ATTR_CUSTOMER_ID ] ] = $one;
            }

        } else {
            $this->_log->debug("There is downline snapshot data for period '$periodValue' ('$periodExists')");
            $result->setPeriodExistsValue($periodExists);
            $result->setErrorCode(ComposeDownlineSnapshotResponse::ERR_NO_ERROR);
        }
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
}