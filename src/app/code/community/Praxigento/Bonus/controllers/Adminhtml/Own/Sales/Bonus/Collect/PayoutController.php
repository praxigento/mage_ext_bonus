<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;

/**
 * Process transactions and collect payouts.
 *
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Adminhtml_Own_Sales_Bonus_Collect_PayoutController extends Mage_Adminhtml_Controller_Action
{
    protected function _construct()
    {
        parent::_construct();
        $this->_setTitile();
    }

    private function _setTitile()
    {
        $this->_title($this->__('Sales'))->_title($this->__('Retail Bonus'))->_title($this->__('Collect Transactions'));
    }

    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function postAction()
    {
        $this->loadLayout();
        /** @var  $hlp Praxigento_Bonus_Helper_Data */
        $hlp = Mage::helper(Config::CFG_HELPER);
        /** @var  $block Praxigento_Bonus_Block_Adminhtml_Own_Sales_Bonus_Collect_Payout_Post */
        $block = Mage::app()->getLayout()->getBlock('prxgt_bonus_sales_bonus_collect_post');

        if ($hlp->cfgRetailBonusEnabled()) {
            /* prevent memory exhausting */
            ini_set('memory_limit', '-1');
            /* process orders */
            $call = Mage::getModel('prxgt_bonus_model/own_service_registry_call');
            $req = Mage::getModel('prxgt_bonus_model/own_service_registry_request_createPayouts');
            $resp = $call->createPayouts($req);
            $count = count($resp->getPayoutIds());
            $block->setCollectedCount($count);
        }
        $this->renderLayout();
    }

}