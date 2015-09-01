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
class Praxigento_Bonus_Test_Helper_Type_UnitTest extends PHPUnit_Framework_TestCase {

    const OPER_ID_BONUS_PV = 1;
    const OPER_ID_ORDER_PV = 2;
    const OPER_ID_ORDER_RETAIL = 3;
    const OPER_ID_PV_FWRD = 4;
    const OPER_ID_PV_INT = 5;
    const OPER_ID_PV_WRITE_OFF = 6;
    const OPER_ID_TRANS_EXT_IN = 7;
    const OPER_ID_TRANS_EXT_OUT = 8;
    const OPER_ID_TRANS_INT = 9;

    const TEST_CODE = 'code';
    const TEST_ID = 21;
    const TEST_NOTE = 'note';

    public function setUp() {
        Config::set(null);
    }

    public function test_getAsset() {
        /** mock stored items  */
        $item    = Config::get()->modelTypeAsset();
        $mockCfg = $this->mockClass('Praxigento_Bonus_Config', 'collectionTypeAsset', $item);
        Config::set($mockCfg);
        /** @var  $hlp Praxigento_Bonus_Helper_Type */
        $hlp   = Config::get()->helperType();
        $asset = $hlp->getAsset(self::TEST_CODE);
        $this->assertTrue($asset instanceof Praxigento_Bonus_Model_Own_Type_Asset);
        $this->assertEquals(self::TEST_ID, $asset->getId());
        $id = $hlp->getAssetId(self::TEST_CODE);
        $this->assertEquals(self::TEST_ID, $id);
    }

    /**
     * Create mock with disabled constructor for class $clazz.
     *
     * @param $clazz
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function mockClass($clazz, $method, $item) {
        $mockBuilder = $this->getMockBuilder($clazz);
        $mockBuilder->setMethods(array( $method ));
        $result = $mockBuilder->getMock();
        /* add item */
        $item->setData(TypeBase::ATTR_CODE, self::TEST_CODE);
        $item->setData(TypeBase::ATTR_ID, self::TEST_ID);
        $item->setData(TypeBase::ATTR_NOTE, self::TEST_NOTE);
        $result->expects($this->once())->method($method)->will($this->returnValue(array( $item )));
        return $result;
    }

    public function test_getCalc() {
        /** mock stored items  */
        /** @var  $item */
        $item    = Config::get()->modelTypeCalc();
        $mockCfg = $this->mockClass('Praxigento_Bonus_Config', 'collectionTypeCalc', $item);
        Config::set($mockCfg);
        /** @var  $hlp Praxigento_Bonus_Helper_Type */
        $hlp  = Config::get()->helperType();
        $type = $hlp->getCalc(self::TEST_CODE);
        $this->assertTrue($type instanceof Praxigento_Bonus_Model_Own_Type_Calc);
        $this->assertEquals(self::TEST_ID, $type->getId());
        $id = $hlp->getCalcId(self::TEST_CODE);
        $this->assertEquals(self::TEST_ID, $id);
    }

    public function test_getOper() {
        /** mock stored items  */
        $this->_mockConfigOper();
        /** @var  $hlp Praxigento_Bonus_Helper_Type */
        $hlp  = Config::get()->helperType();
        $type = $hlp->getOper(Config::OPER_BONUS_PV);
        $this->assertTrue($type instanceof Praxigento_Bonus_Model_Own_Type_Oper);
        $this->assertEquals(self::OPER_ID_BONUS_PV, $type->getId());
        $id = $hlp->getOperId(Config::OPER_TRANS_INT);
        $this->assertEquals(self::OPER_ID_TRANS_INT, $id);
    }

    public function test_getOperIdsForPvWriteOff() {
        /** mock stored items  */
        $this->_mockConfigOper();
        /** @var  $hlp Praxigento_Bonus_Helper_Type */
        $hlp   = Config::get()->helperType();
        $types = $hlp->getOperIdsForPvWriteOff();
        $this->assertTrue(is_array($types));
        $this->assertContains(self::OPER_ID_ORDER_PV, $types);
        $this->assertContains(self::OPER_ID_PV_INT, $types);
        $this->assertContains(self::OPER_ID_PV_FWRD, $types);
        $this->assertNotContains(self::OPER_ID_BONUS_PV, $types);
    }

    /**
     * Mock Praxigento_Bonus_Config to return
     */
    private function _mockConfigOper() {
        /** mock config class with collection for operations types  */
        $mockBuilder = $this->getMockBuilder('Praxigento_Bonus_Config');
        $mockBuilder->setMethods(array( 'collectionTypeOper' ));
        $mockCfg = $mockBuilder->getMock();
        $items   = array();
        /* add operation types */
        /* OPER_BONUS_PV */
        /** @var  $item Praxigento_Bonus_Model_Own_Type_Oper */
        $item = Config::get()->modelTypeOper();
        $item->setData(TypeBase::ATTR_CODE, Config::OPER_BONUS_PV);
        $item->setData(TypeBase::ATTR_ID, self::OPER_ID_BONUS_PV);
        $items[] = $item;
        /* OPER_ORDER_PV */
        $item = Config::get()->modelTypeOper();
        $item->setData(TypeBase::ATTR_CODE, Config::OPER_ORDER_PV);
        $item->setData(TypeBase::ATTR_ID, self::OPER_ID_ORDER_PV);
        $items[] = $item;
        /* OPER_ORDER_RETAIL */
        $item = Config::get()->modelTypeOper();
        $item->setData(TypeBase::ATTR_CODE, Config::OPER_ORDER_RETAIL);
        $item->setData(TypeBase::ATTR_ID, self::OPER_ID_ORDER_RETAIL);
        $items[] = $item;
        /* OPER_PV_FWRD */
        $item = Config::get()->modelTypeOper();
        $item->setData(TypeBase::ATTR_CODE, Config::OPER_PV_FWRD);
        $item->setData(TypeBase::ATTR_ID, self::OPER_ID_PV_FWRD);
        $items[] = $item;
        /* OPER_PV_INT */
        $item = Config::get()->modelTypeOper();
        $item->setData(TypeBase::ATTR_CODE, Config::OPER_PV_INT);
        $item->setData(TypeBase::ATTR_ID, self::OPER_ID_PV_INT);
        $items[] = $item;
        /* OPER_PV_WRITE_OFF */
        $item = Config::get()->modelTypeOper();
        $item->setData(TypeBase::ATTR_CODE, Config::OPER_PV_WRITE_OFF);
        $item->setData(TypeBase::ATTR_ID, self::OPER_ID_PV_WRITE_OFF);
        $items[] = $item;
        /* OPER_TRANS_EXT_IN */
        $item = Config::get()->modelTypeOper();
        $item->setData(TypeBase::ATTR_CODE, Config::OPER_TRANS_EXT_IN);
        $item->setData(TypeBase::ATTR_ID, self::OPER_ID_TRANS_EXT_IN);
        $items[] = $item;
        /* OPER_TRANS_EXT_OUT */
        $item = Config::get()->modelTypeOper();
        $item->setData(TypeBase::ATTR_CODE, Config::OPER_TRANS_EXT_OUT);
        $item->setData(TypeBase::ATTR_ID, self::OPER_ID_TRANS_EXT_OUT);
        $items[] = $item;
        /* OPER_TRANS_INT */
        $item = Config::get()->modelTypeOper();
        $item->setData(TypeBase::ATTR_CODE, Config::OPER_TRANS_INT);
        $item->setData(TypeBase::ATTR_ID, self::OPER_ID_TRANS_INT);
        $items[] = $item;
        //
        $mockCfg->expects($this->any())->method('collectionTypeOper')
                ->will($this->returnValue($items));
        Config::set($mockCfg);
    }

    public function test_getPeriod() {
        /** mock stored items  */
        /** @var  $item */
        $item    = Config::get()->modelTypePeriod();
        $mockCfg = $this->mockClass('Praxigento_Bonus_Config', 'collectionTypePeriod', $item);
        Config::set($mockCfg);
        /** @var  $hlp Praxigento_Bonus_Helper_Type */
        $hlp  = Config::get()->helperType();
        $type = $hlp->getPeriod(self::TEST_CODE);
        $this->assertTrue($type instanceof Praxigento_Bonus_Model_Own_Type_Period);
        $this->assertEquals(self::TEST_ID, $type->getId());
        $id = $hlp->getPeriodId(self::TEST_CODE);
        $this->assertEquals(self::TEST_ID, $id);
    }
}