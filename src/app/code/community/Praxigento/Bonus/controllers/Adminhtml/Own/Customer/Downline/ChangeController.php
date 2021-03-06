<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Nmmlm_Core_Config as CoreConfig;
use Praxigento_Bonus_Block_Adminhtml_Own_Customer_Downline_Change_Base as BlockBase;
use Praxigento_Bonus_Block_Adminhtml_Own_Customer_Downline_Change_Index as BlockIndex;
use Praxigento_Bonus_Block_Adminhtml_Own_Customer_Downline_Change_Preview as BlockPreview;
use Praxigento_Bonus_Block_Adminhtml_Own_Customer_Downline_Change_Result as BlockResult;
use Praxigento_Bonus_Config as Config;

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Adminhtml_Own_Customer_Downline_ChangeController
    extends Mage_Adminhtml_Controller_Action {

    const BLOCK = 'prxgt_bonus_downline_change';
    /**
     * Customer for whom upline is changed.
     *
     * @var  Nmmlm_Core_Model_Customer_Customer
     */
    private $_customer;
    /**
     * New Upline to change.
     *
     * @var  Nmmlm_Core_Model_Customer_Customer
     */
    private $_customerUpline;

    /**
     * Compose information page before validation.
     */
    public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Perform requested changes.
     */
    public function resultAction() {
        $this->loadLayout();
        /** @var  $block BlockResult */
        $block = $this->getLayout()->getBlock(self::BLOCK);
        /* get posted data*/
        $post = $this->getRequest()->getPost();
        $customerId = isset($post[ BlockIndex::DOM_FLD_CUSTOMER_ID ]) ?
            trim($post[ BlockIndex::DOM_FLD_CUSTOMER_ID ]) : null;
        $newUplineId = isset($post[ BlockIndex::DOM_FLD_UPLINE_ID ]) ?
            trim($post[ BlockIndex::DOM_FLD_UPLINE_ID ]) : null;
        /* validate posted data and init block */
        $this->_validatePostedData($customerId, $newUplineId, $block);
        /* perform operation if no error are found */
        if(!$block->getIsErrorFound()) {
            /** @var  $call Praxigento_Bonus_Service_Snapshot_Call */
            $call = Config::get()->serviceSnapshot();
            /** @var  $req ChangeUplineRequest */
            $req = $call->requestChangeUpline();
            $req->setCustomerId($this->_customer->getId());
            $req->setNewUplineId($this->_customerUpline->getId());
            $resp = $call->changeUpline($req);
            if($resp->isSucceed()) {
                $block->setIsTreeUpdated(true);
            } else {
                $block->setIsTreeUpdated(false);
                $block->setErrorMessage($resp->getErrorCode() . ': ' . $resp->getErrorMessage());
            }
        }
        $this->renderLayout();
    }

    /**
     * @return mixed
     */
    protected function _isAllowed() {
        $result = Config::get()->singleton('admin/session')->isAllowed(Config::ACL_CUSTOMER_TREE_CHANGE);
        return $result;
    }

    /**
     * Analyze posted data and validate re-linking conditions.
     */
    public function previewAction() {
        $this->loadLayout();
        /** @var  $block BlockPreview */
        $block = $this->getLayout()->getBlock(self::BLOCK);
        /* get posted data*/
        $post = $this->getRequest()->getPost();
        $customerMlmId = isset($post[ BlockIndex::DOM_FLD_CUSTOMER_ID ]) ?
            trim($post[ BlockIndex::DOM_FLD_CUSTOMER_ID ]) : null;
        $newUplineMlmId = isset($post[ BlockIndex::DOM_FLD_UPLINE_ID ]) ?
            trim($post[ BlockIndex::DOM_FLD_UPLINE_ID ]) : null;
        /* validate posted data and init block */
        $this->_validatePostedData($customerMlmId, $newUplineMlmId, $block);
        $this->renderLayout();
    }

    private function _validatePostedData($customerMlmtId, $newUplineMlmId, BlockBase $block) {
        /** @var  $hlp Praxigento_Bonus_Helper_Data */
        $hlp = Config::get()->helper();
        /** @var  $hlpCore Nmmlm_Core_Helper_Data */
        $hlpCore = CoreConfig::get()->helper();
        $block->setCurrentCustomerId($customerMlmtId);
        $block->setNewUplineId($newUplineMlmId);
        /* check posted data and populate current block with data from DB */
        $block->setIsFoundCurrentCustomer(false);
        $block->setIsFoundCurrentUpline(false);
        $block->setIsFoundNewUpline(false);
        $block->setIsErrorFound(true);
        /* lookup for new upline by MLM ID */
        $attrs = array( 'firstname', 'middlename', 'lastname' );
        /** @var  $custUplineNew Nmmlm_Core_Model_Customer_Customer */
        $custUplineNew = $hlpCore->getCustomerByMlmId($newUplineMlmId, $attrs);
        if(strlen($newUplineMlmId) > 0 && ($custUplineNew->getNmmlmCoreMlmId() == $newUplineMlmId)) {
            $this->_customerUpline = $custUplineNew;
            $block->setIsFoundNewUpline(true);
            $block->setNewUplineName($custUplineNew->getName());
        }
        /* lookup for current customer by MLM ID */
        /** @var  $custItself Nmmlm_Core_Model_Customer_Customer */
        $custItself = $hlpCore->getCustomerByMlmId($customerMlmtId, $attrs);
        if(strlen($customerMlmtId) > 0 && ($custItself->getNmmlmCoreMlmId() == $customerMlmtId)) {
            $this->_customer = $custItself;
            $block->setIsFoundCurrentCustomer(true);
            $block->setCurrentCustomerName($custItself->getName());
            /* lookup for current upline customer in the downline snapshots */
            $custUplineCurr = $hlp->getUplineForCustomer($custItself->getId(), Config::PERIOD_KEY_NOW, $attrs);
            $currentUplineId = $custUplineCurr->getNmmlmCoreMlmId();
            $block->setCurrentUplineId($currentUplineId);
            /** @var  $currentUpline Nmmlm_Core_Model_Customer_Customer */
            $currentUpline = $hlpCore->getCustomerById($custUplineCurr->getId(), $attrs);
            if(strlen($currentUplineId) > 0 && ($currentUpline->getNmmlmCoreMlmId() == $currentUplineId)) {
                $block->setCurrentUplineName($currentUpline->getName());
            }
            /* validate conditions for Upline change */
            $custPath = $custItself->getData(CoreConfig::ATTR_CUST_MLM_PATH);
            $newUplinePath = $custUplineNew->getData(CoreConfig::ATTR_CUST_MLM_PATH);
            if($customerMlmtId == $newUplineMlmId) {
                $block->setErrorMessage('Customer cannot be linked to itself.');
            } else if($currentUplineId == $newUplineMlmId) {
                $block->setErrorMessage('Customer is already linked to the same upline.');
            } else if(strstr($newUplinePath, $custPath . $custItself->getId() . Config::MPS)) {
                $block->setErrorMessage('Customer cannot be linked to own downline.');
            } else {
                $block->setIsErrorFound(false);
            }
        }
    }

}