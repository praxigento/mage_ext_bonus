<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;

/**
 * Process collected payouts and create eWallet payments.
 *
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Adminhtml_Own_Sales_Bonus_PaymentController extends Mage_Adminhtml_Controller_Action
{
    protected function _construct()
    {
        parent::_construct();
        $this->_setTitile();
    }

    private function _setTitile()
    {
        $this->_title($this->__('Sales'))->_title($this->__('Retail Bonus'))->_title($this->__('eWallet Payments'));
    }

    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function postAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

}