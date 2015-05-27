<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Block_Adminhtml_Own_Sales_Bonus_Collect_Base
    extends Praxigento_Bonus_Block_Adminhtml_Own_Base
{
    /** @var  Praxigento_Bonus_Model_Own_Service_Registry_Call */
    private $_registryCall;

    protected function getRegistryCall()
    {
        if (is_null($this->_registryCall)) {
            $this->_registryCall = Mage::getModel('prxgt_bonus_model/own_service_registry_call');
        }
        return $this->_registryCall;
    }

    public function isRetailBonusEnabled()
    {
        /** @var  $hlp Praxigento_Bonus_Helper_Data */
        $hlp = Mage::helper(Config::CFG_HELPER);
        $result = $hlp->cfgRetailBonusEnabled();
        return $result;
    }
}