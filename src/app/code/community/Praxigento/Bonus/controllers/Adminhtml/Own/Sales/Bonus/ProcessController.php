<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */

/**
 * Process orders and calculate retail bonuses.
 *
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Adminhtml_Own_Sales_Bonus_ProcessController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
}