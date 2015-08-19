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
        $id = $hlp->getAssetId(self::TEST_CODE);
        $this->assertEquals(self::TEST_ID, $id);
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
        $id = $hlp->getCalcId(self::TEST_CODE);
        $this->assertEquals(self::TEST_ID, $id);
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
        $id = $hlp->getOperId(self::TEST_CODE);
        $this->assertEquals(self::TEST_ID, $id);
    }

    public function test_getOperIdsForPvWriteOff()
    {
        /** mock stored items  */
        $mockBuilder = $this->getMockBuilder('Praxigento_Bonus_Config');
        $mockBuilder->setMethods(array('collectionTypeOper'));
        $mockCfg = $mockBuilder->getMock();
        /* add item */
        /** @var  $item Praxigento_Bonus_Model_Own_Type_Oper */
        $itemOrdrPv = Config::get()->modelTypeOper();
        $itemOrdrPv->setData(TypeBase::ATTR_CODE, Config::OPER_ORDER_PV);
        $itemOrdrPv->setData(TypeBase::ATTR_ID, 100);
        /** @var  $item Praxigento_Bonus_Model_Own_Type_Oper */
        $itemPvInt = Config::get()->modelTypeOper();
        $itemPvInt->setData(TypeBase::ATTR_CODE, Config::OPER_PV_INT);
        $itemPvInt->setData(TypeBase::ATTR_ID, 200);
        /** @var  $item Praxigento_Bonus_Model_Own_Type_Oper */
        $itemPvFwrd = Config::get()->modelTypeOper();
        $itemPvFwrd->setData(TypeBase::ATTR_CODE, Config::OPER_PV_FWRD);
        $itemPvFwrd->setData(TypeBase::ATTR_ID, 300);
        /** @var  $item Praxigento_Bonus_Model_Own_Type_Oper */
        $itemOther = Config::get()->modelTypeOper();
        $itemOther->setData(TypeBase::ATTR_CODE, Config::OPER_PV_WRITE_OFF);
        $itemOther->setData(TypeBase::ATTR_ID, 500);
        //
        $mockCfg->expects($this->once())->method('collectionTypeOper')
            ->will($this->returnValue(array($itemOrdrPv, $itemPvInt, $itemPvFwrd, $itemOther)));
        Config::set($mockCfg);
        /** @var  $hlp Praxigento_Bonus_Helper_Type */
        $hlp = Config::get()->helperType();
        $types = $hlp->getOperIdsForPvWriteOff();
        $this->assertTrue(is_array($types));
        $this->assertContains(100, $types);
        $this->assertContains(200, $types);
        $this->assertContains(300, $types);
        $this->assertNotContains(500, $types);
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
        $id = $hlp->getPeriodId(self::TEST_CODE);
        $this->assertEquals(self::TEST_ID, $id);
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