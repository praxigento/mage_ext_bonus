<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
include_once('../phpunit_bootstrap.php');

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Test_Helper_Data_DvlpTest extends PHPUnit_Framework_TestCase
{

    public function test_getUplineFromSession()
    {
        $helper = Mage::helper('prxgt_bonus_helper');
        $upline = $helper->getUplineFromSession();
    }
}