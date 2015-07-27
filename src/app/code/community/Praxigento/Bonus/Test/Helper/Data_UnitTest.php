<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;

include_once('../phpunit_bootstrap.php');

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Test_Helper_Data_UnitTest extends PHPUnit_Framework_TestCase
{

    public function test_cfg()
    {
        /** @var  $hlp Praxigento_Bonus_Helper_Data */
        $hlp = Mage::helper(Config::CFG_HELPER);
        $this->assertTrue(is_numeric($hlp->cfgGeneralDownlineDepth()));
        $this->assertTrue(is_bool($hlp->cfgPersonalBonusEnabled()));
        $this->assertTrue(is_bool($hlp->cfgRetailBonusEnabled()));
        $this->assertTrue(is_numeric($hlp->cfgRetailBonusFeeFixed()));
        $this->assertTrue(is_numeric($hlp->cfgRetailBonusFeeMax()));
        $this->assertTrue(is_numeric($hlp->cfgRetailBonusFeeMin()));
        $this->assertTrue(is_numeric($hlp->cfgRetailBonusFeePercent()));
    }
}