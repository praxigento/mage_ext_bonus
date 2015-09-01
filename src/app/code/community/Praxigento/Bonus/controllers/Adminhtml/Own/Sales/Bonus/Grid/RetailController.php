<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;

/**
 * Display Retail Bonus grid.
 *
 * Thanks "inchoo" guys (http://inchoo.net/magento/how-to-create-a-custom-grid-from-scratch/)
 *
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Adminhtml_Own_Sales_Bonus_Grid_RetailController extends Mage_Adminhtml_Controller_Action {
    public function indexAction() {
        $this->loadLayout();
        $this->_title($this->__('Sales'))->_title($this->__('Retail Bonus'));
        /* see ./etc/adminhtml.xml::/config/acl/resources/admin/children/sales/children/prxgt_bonus/... */
        $this->_setActiveMenu('sales/prxgt_bonus/retail_grid');
        $this->renderLayout();
    }
}