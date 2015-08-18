<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;
use Praxigento_Bonus_Model_Own_Source_Weekday as Weekday;
use Praxigento_Bonus_Model_Own_Type_Asset as TypeAsset;
use Praxigento_Bonus_Model_Own_Type_Base as TypeBase;
use Praxigento_Bonus_Model_Own_Type_Calc as TypeCalc;

include_once('../phpunit_bootstrap.php');

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Test_Helper_Account_UnitTest extends PHPUnit_Framework_TestCase
{

    public function test_constructor()
    {
        $hlp = Config::get()->helperAccount();
        $this->assertNotNull($hlp);
        $this->assertTrue($hlp instanceof Praxigento_Bonus_Helper_Account);
    }

    public function test_getAccountantAccByAssetCode()
    {
        $hlp = Config::get()->helperAccount();
        $hlp->getAccountantAccByAssetCode(Config::ASSET_PV);
        $this->assertNotNull($hlp);
        $this->assertTrue($hlp instanceof Praxigento_Bonus_Helper_Account);
    }
}