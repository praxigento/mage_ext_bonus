<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
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
    public function previewAction() {
        $this->loadLayout();
        /** @var  $req Nmmlm_Core_Model_Own_Service_Tree_Validation_Request_Validate */
        $req = Mage::getModel('nmmlm_core_model/own_service_tree_validation_request_validate');
        /** @var  $call Nmmlm_Core_Model_Own_Service_Tree_Validation_Call */
        $call = Mage::getModel('nmmlm_core_model/own_service_tree_validation_call');
        /** @var  $resp Nmmlm_Core_Model_Own_Service_Tree_Validation_Response_Validate */
        $resp = $call->validate($req);
        $entries = $resp->getEntries();
        /** @var  $block Nmmlm_Core_Block_Adminhtml_Own_Customer_Tree_Validation_Preview */
        $block = $this->getLayout()->getBlock(self::BLOCK);
        $block->setInvalidEntries($entries);
        $this->renderLayout();
    }

    /**
     * Compose page with error fixing results.
     */
    public function resultAction() {
        $this->loadLayout();
        /** @var  $req Nmmlm_Core_Model_Own_Service_Tree_Validation_Request_Fix */
        $req = Mage::getModel('nmmlm_core_model/own_service_tree_validation_request_fix');
        /** @var  $call Nmmlm_Core_Model_Own_Service_Tree_Validation_Call */
        $call = Mage::getModel('nmmlm_core_model/own_service_tree_validation_call');
        /** @var  $resp Nmmlm_Core_Model_Own_Service_Tree_Validation_Response_Fix */
        $resp = $call->fix($req);
        $entries = $resp->getEntries();
        /** @var  $block Nmmlm_Core_Block_Adminhtml_Own_Customer_Tree_Validation_Result */
        $block = $this->getLayout()->getBlock(self::BLOCK);
        $block->setInvalidEntries($entries);
        $this->renderLayout();
    }
}