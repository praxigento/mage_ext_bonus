<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Block_Adminhtml_Own_Base extends Nmmlm_Core_Block_Adminhtml_Base
{

    public function isRetailBonusEnabled()
    {
        /** @var  $hlp Praxigento_Bonus_Helper_Data */
        $hlp = Mage::helper(Config::CFG_HELPER);
        $result = $hlp->cfgRetailBonusEnabled();
        return $result;
    }

    public function isPersonalBonusEnabled()
    {
        /** @var  $hlp Praxigento_Bonus_Helper_Data */
        $hlp = Mage::helper(Config::CFG_HELPER);
        $result = $hlp->cfgPersonalBonusEnabled();
        return $result;
    }
}