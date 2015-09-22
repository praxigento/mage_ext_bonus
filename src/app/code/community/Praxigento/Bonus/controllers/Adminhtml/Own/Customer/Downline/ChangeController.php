<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Nmmlm_Core_Config as CoreConfig;
use Praxigento_Bonus_Block_Adminhtml_Own_Customer_Downline_Change_Base as BlockBase;
use Praxigento_Bonus_Block_Adminhtml_Own_Customer_Downline_Change_Index as BlockIndex;
use Praxigento_Bonus_Block_Adminhtml_Own_Customer_Downline_Change_Preview as BlockPreview;
use Praxigento_Bonus_Config as Config;

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Adminhtml_Own_Customer_Downline_ChangeController
    extends Mage_Adminhtml_Controller_Action {

    const BLOCK = 'prxgt_bonus_downline_change';

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
        $currentId = isset($post[ BlockIndex::DOM_FLD_CUSTOMER_ID ]) ?
            trim($post[ BlockIndex::DOM_FLD_CUSTOMER_ID ]) : null;
        $newUplineId = isset($post[ BlockIndex::DOM_FLD_UPLINE_ID ]) ?
            trim($post[ BlockIndex::DOM_FLD_UPLINE_ID ]) : null;
        /* validate posted data and init block */
        $this->_validatePostedData($currentId, $newUplineId, $block);
        $this->renderLayout();
    }

    private function _validatePostedData($currentId, $newUplineId, BlockBase $block) {
        /** @var  $hlpCore Nmmlm_Core_Helper_Data */
        $hlpCore = CoreConfig::get()->helper();
        $block->setCurrentCustomerId($currentId);
        $block->setNewUplineId($newUplineId);
        /* check posted data and populate current block with data from DB */
        $block->setIsFoundCurrentCustomer(false);
        $block->setIsFoundCurrentUpline(false);
        $block->setIsFoundNewUpline(false);
        $block->setIsErrorFound(false);
        /* lookup for new upline */
        /** @var  $newUpline Nmmlm_Core_Model_Customer_Customer */
        //        $newUpline = Nmmlm_Core_Util::findCustomerByMlmId($newUplineId);
        $newUpline = $hlpCore->getCustomerByMlmId($newUplineId, '*');
        if(strlen($newUplineId) && ($newUpline->getNmmlmCoreMlmId() == $newUplineId)) {
            $this->_customerUpline = $newUpline;
            $block->setIsFoundNewUpline(true);
            $block->setNewUplineName($newUpline->getName());
        }
        /* lookup for current customer and current upline */
        /** @var  $currentCustomer Nmmlm_Core_Model_Customer_Customer */
        $currentCustomer = $hlpCore->getCustomerByMlmId($currentId);
        if(strlen($currentId) && ($currentCustomer->getNmmlmCoreMlmId() == $currentId)) {
            $this->_customerCurrent = $currentCustomer;
            $block->setIsFoundCurrentCustomer(true);
            $block->setCurrentCustomerName($currentCustomer->getName());
            $currentUplineId = $currentCustomer->getNmmlmCoreMlmUpline();
            $block->setCurrentUplineId($currentUplineId);
            /** @var  $currentUpline Nmmlm_Core_Model_Customer_Customer */
            $currentUpline = $hlpCore->getCustomerByMlmId($currentUplineId);
            if(strlen($currentUplineId) && ($currentUpline->getNmmlmCoreMlmId() == $currentUplineId)) {
                $block->setCurrentUplineName($currentUpline->getName());
            }
            /* validate conditions for Upline change */
            $newUplinePath = $newUpline->getNmmlmCoreMlmPath();
            if($currentId == $newUplineId) {
                $block->setIsErrorFound(true);
                $block->setErrorMessage('Customer cannot be linked to itself.');
            } else if($currentUplineId == $newUplineId) {
                $block->setIsErrorFound(true);
                $block->setErrorMessage('Customer is already linked to the same upline.');
            } else if(strstr($newUplinePath, Config::MPS . $currentId . Config::MPS)) {
                $block->setIsErrorFound(true);
                $block->setErrorMessage('Customer cannot be linked to own downline.');
            }
        }
    }

}