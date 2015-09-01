<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;

include_once('../../../phpunit_bootstrap.php');

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Test_Model_Own_Source_Weekday_UnitTest extends PHPUnit_Framework_TestCase {

    public function test_constructor() {
        /** @var  $model Praxigento_Bonus_Model_Own_Source_Weekday */
        $model = Config::get()->model('prxgt_bonus_model/source_weekday');
        $this->assertNotNull($model);
        $this->assertTrue($model instanceof Praxigento_Bonus_Model_Own_Source_Weekday);
    }

    public function test_toOptionArray() {
        /** @var  $model Praxigento_Bonus_Model_Own_Source_Weekday */
        $model   = Config::get()->model('prxgt_bonus_model/source_weekday');
        $options = $model->toOptionArray();
        $this->assertTrue(is_array($options));
        $this->assertEquals(7, count($options));
    }

}