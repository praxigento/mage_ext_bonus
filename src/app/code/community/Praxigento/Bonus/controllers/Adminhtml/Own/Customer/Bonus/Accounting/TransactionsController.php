<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;

/**
 * Display Transactions grid.
 *
 * Thanks "inchoo" guys (http://inchoo.net/magento/how-to-create-a-custom-grid-from-scratch/)
 *
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Adminhtml_Own_Customer_Bonus_Accounting_TransactionsController
    extends Mage_Adminhtml_Controller_Action {

    public function indexAction() {
        $this->loadLayout();
        $this->_title($this->__('Customers'))
             ->_title($this->__('Bonus'))
             ->_title($this->__('Accounting'))
             ->_title($this->__('Transactions'));
        /* see ./etc/adminhtml.xml::/config/acl/resources/admin/children/customer/children/prxgt_bonus/... */
        $this->_setActiveMenu('customer/prxgt_bonus/accounting/transactions');
        $this->renderLayout();
    }
}