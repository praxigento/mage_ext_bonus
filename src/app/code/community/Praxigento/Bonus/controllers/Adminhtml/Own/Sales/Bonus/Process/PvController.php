<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;

/**
 * Process Personal Volume bonus.
 *
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Adminhtml_Own_Sales_Bonus_Process_PvController extends Mage_Adminhtml_Controller_Action {

    public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function postAction() {
        $this->loadLayout();
        $call = Config::get()->serviceCalculation();
        /** @var  $resp Praxigento_Bonus_Service_Calculation_Response_CalcPvWriteOff */
        $resp = $call->calcPvWriteOff();
        /** @var  $block Praxigento_Bonus_Block_Adminhtml_Own_Sales_Bonus_Process_Pv_Post */
        $block = Mage::app()->getLayout()->getBlock('sales_bonus_process_pv');
        if($resp->isSucceed()) {
            $block->setIsCalculationDone(true);
        } else {
            $block->setIsCalculationDone(false);
        }

        $this->renderLayout();
    }

}