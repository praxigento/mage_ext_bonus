<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Block_Adminhtml_Own_Customer_Downline_Validation_Index as BlockIndex;
use Praxigento_Bonus_Config as Config;

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Adminhtml_Own_Customer_Downline_ValidationController
    extends Mage_Adminhtml_Controller_Action {

    const BLOCK = 'prxgt_bonus_downline_validation';

    /**
     * Compose information page before validation.
     */
    public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * @return mixed
     */
    protected function _isAllowed() {
        $result = Config::get()->singleton('admin/session')->isAllowed(Config::ACL_CUSTOMER_TREE_VALIDATION);
        return $result;
    }

    /**
     * Compose page with validation results.
     */
    public function resultAction() {
        $this->loadLayout();

        $selectedPeriod = $this->getRequest()->getParam(BlockIndex::DOM_SELECT);
        /** @var  $call Praxigento_Bonus_Service_Snapshot_Call */
        $call = Config::get()->serviceSnapshot();
        /** @var  $req Praxigento_Bonus_Service_Snapshot_Request_ValidateDownlineSnapshot */
        $req = $call->requestValidateDownlineSnapshot();
        $req->setPeriodValue($selectedPeriod);
        /** @var  $resp Praxigento_Bonus_Service_Snapshot_Response_ValidateDownlineSnapshot */
        $resp = $call->validateDownlineSnapshot($req);
        /** @var  $block Praxigento_Bonus_Block_Adminhtml_Own_Customer_Downline_Validation_Result */
        $block = $this->getLayout()->getBlock(self::BLOCK);
        $block->setPeriodValue($selectedPeriod);
        $block->setTotalCustomers($resp->getTotalCustomers());
        $block->setMaxDepth($resp->getMaxDepth());
        $block->setTotalRoots($resp->getTotalRoots());
        $block->setTotalOrphans($resp->getTotalOrphans());
        $block->setTotalWrongPaths($resp->getTotalWrongPaths());
        $this->renderLayout();
    }
}