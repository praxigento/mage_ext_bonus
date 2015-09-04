<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Block_Adminhtml_Own_Customer_Bonus_Accounting_Transactions
    extends Mage_Adminhtml_Block_Widget_Grid_Container {

    function __construct() {
        $this->_blockGroup = Config::CFG_BLOCK;
        $this->_controller = 'adminhtml_own_customer_bonus_accounting_transactions';
        $this->_headerText = $this->__('Transactions');
        parent::__construct();
        $this->_removeButton('add');
    }
}