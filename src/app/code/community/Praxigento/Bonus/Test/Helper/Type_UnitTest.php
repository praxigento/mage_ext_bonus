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
class Praxigento_Bonus_Test_Helper_Type_UnitTest extends PHPUnit_Framework_TestCase
{

    const TEST_CODE = 'code';
    const  TEST_ID = 21;
    const TEST_NOTE = 'note';

    public function test_getAsset()
    {
        /** mock stored items  */
        $item = Config::get()->modelTypeAsset();
        $mockCfg = $this->mockClass('Praxigento_Bonus_Config', 'collectionTypeAsset', $item);
        Config::set($mockCfg);
        /** @var  $hlp Praxigento_Bonus_Helper_Type */
        $hlp = Config::get()->helperType();
        $asset = $hlp->getAsset(self::TEST_CODE);
        $this->assertTrue($asset instanceof Praxigento_Bonus_Model_Own_Type_Asset);
        $this->assertEquals(self::TEST_ID, $asset->getId());
    }

    public function test_getCalc()
    {
        /** mock stored items  */
        /** @var  $item */
        $item = Config::get()->modelTypeCalc();
        $mockCfg = $this->mockClass('Praxigento_Bonus_Config', 'collectionTypeCalc', $item);
        Config::set($mockCfg);
        /** @var  $hlp Praxigento_Bonus_Helper_Type */
        $hlp = Config::get()->helperType();
        $type = $hlp->getCalc(self::TEST_CODE);
        $this->assertTrue($type instanceof Praxigento_Bonus_Model_Own_Type_Calc);
        $this->assertEquals(self::TEST_ID, $type->getId());
    }

    public function test_getOper()
    {
        /** mock stored items  */
        /** @var  $item */
        $item = Config::get()->modelTypeOper();
        $mockCfg = $this->mockClass('Praxigento_Bonus_Config', 'collectionTypeOper', $item);
        Config::set($mockCfg);
        /** @var  $hlp Praxigento_Bonus_Helper_Type */
        $hlp = Config::get()->helperType();
        $type = $hlp->getOper(self::TEST_CODE);
        $this->assertTrue($type instanceof Praxigento_Bonus_Model_Own_Type_Oper);
        $this->assertEquals(self::TEST_ID, $type->getId());
    }

    public function test_getPeriod()
    {
        /** mock stored items  */
        /** @var  $item */
        $item = Config::get()->modelTypePeriod();
        $mockCfg = $this->mockClass('Praxigento_Bonus_Config', 'collectionTypePeriod', $item);
        Config::set($mockCfg);
        /** @var  $hlp Praxigento_Bonus_Helper_Type */
        $hlp = Config::get()->helperType();
        $type = $hlp->getPeriod(self::TEST_CODE);
        $this->assertTrue($type instanceof Praxigento_Bonus_Model_Own_Type_Period);
        $this->assertEquals(self::TEST_ID, $type->getId());
    }

    /**
     * Create mock with disabled constructor for class $clazz.
     * @param $clazz
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function mockClass($clazz, $method, $item)
    {
        $mockBuilder = $this->getMockBuilder($clazz);
        $mockBuilder->setMethods(array($method));
        $result = $mockBuilder->getMock();
        /* add item */
        $item->setData(TypeBase::ATTR_CODE, self::TEST_CODE);
        $item->setData(TypeBase::ATTR_ID, self::TEST_ID);
        $item->setData(TypeBase::ATTR_NOTE, self::TEST_NOTE);
        $result->expects($this->once())->method($method)->will($this->returnValue(array($item)));
        return $result;
    }
}