<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Block_Adminhtml_Own_Customer_Downline_TreeValidation_Preview
    extends Praxigento_Bonus_Block_Adminhtml_Own_Base
{
    /** @var  Nmmlm_Core_Model_Own_Service_Tree_Validation_Bean_ErrorEntry[] */
    private $_invalidEntries;

    /**
     * @return Nmmlm_Core_Model_Own_Service_Tree_Validation_Bean_ErrorEntry[]
     */
    public function getInvalidEntries()
    {
        return $this->_invalidEntries;
    }

    /**
     * @param Nmmlm_Core_Model_Own_Service_Tree_Validation_Bean_ErrorEntry[] $val
     */
    public function setInvalidEntries($val)
    {
        $this->_invalidEntries = $val;
    }

    public function uiTitle()
    {
        echo $this->__('Customer Tree Validation Result');
    }
}