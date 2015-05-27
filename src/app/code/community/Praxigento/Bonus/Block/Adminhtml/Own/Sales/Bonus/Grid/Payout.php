<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Block_Adminhtml_Own_Sales_Bonus_Grid_Payout
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    function __construct()
    {
        $this->_blockGroup = Config::CFG_BLOCK;
        $this->_controller = 'adminhtml_own_sales_bonus_grid_payout';
        $this->_headerText = $this->__('Bonus Payouts');
        parent::__construct();
        $this->_removeButton('add');
    }
}