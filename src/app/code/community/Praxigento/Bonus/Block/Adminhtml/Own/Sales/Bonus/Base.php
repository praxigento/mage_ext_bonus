<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */


/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Block_Adminhtml_Own_Sales_Bonus_Base
    extends Praxigento_Bonus_Block_Adminhtml_Own_Base
{
    /** @var  Praxigento_Bonus_Model_Own_Service_Registry_Call */
    private $_registryCall;

    protected function getRegistryCall()
    {
        if (is_null($this->_registryCall)) {
            $this->_registryCall = Mage::getModel('prxgt_bonus_model/service_registry_call');
        }
        return $this->_registryCall;
    }
}