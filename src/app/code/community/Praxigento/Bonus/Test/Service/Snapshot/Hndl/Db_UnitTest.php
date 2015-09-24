<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;
use Praxigento_Bonus_Model_Own_Log_Calc as LogCalc;
use Praxigento_Bonus_Service_Operations_Response_GetOperationsForPvWriteOff as GetOperationsForPvWriteOffResponse;
use Praxigento_Bonus_Service_Period_Response_GetPeriodForPvWriteOff as GetPeriodForPvWriteOffResponse;

include_once('../../../phpunit_bootstrap.php');

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Test_Service_Snapshot_Hndl_Db_UnitTest
    extends PHPUnit_Framework_TestCase {

    /**
     * Reset Config before each test.
     */
    public function setUp() {
        Config::set(null);
    }

    public function test_constructor() {
        /**
         * Create mocks (direct order).
         */

        /** @var  $hndl Praxigento_Bonus_Service_Snapshot_Hndl_Db */
        $hndl = new Praxigento_Bonus_Service_Snapshot_Hndl_Db();
        $this->assertNotNull($hndl);
    }

    public function test_isThereDownlinesSnapForPeriod_exactEquality() {
        $PERIOD = '20150601';
        $FOUND = "21";
        /**
         * Create mocks (direct order).
         */
        /* Config:: */
        $mockCfg = $this
            ->getMockBuilder('Praxigento_Bonus_Config')
            ->setMethods(array( 'tableName', 'connectionWrite' ))
            ->getMock();
        // $conn = $cfg->connectionWrite();
        $mockConn = $this
            ->getMockBuilder('Magento_Db_Adapter_Pdo_Mysql')
            ->disableOriginalConstructor()
            ->setMethods(array( 'fetchOne' ))
            ->getMock();
        $mockCfg
            ->expects($this->once())
            ->method('connectionWrite')
            ->will($this->returnValue($mockConn));
        // $tbl       = $rsrc->getTableName(Config::CFG_MODEL . '/' . Config::ENTITY_SNAP_DOWNLINE);
        $mockCfg
            ->expects($this->once())
            ->method('tableName')
            ->will($this->returnValue('prxgt_bonus_snap_downline'));
        // $rs        = $conn->fetchOne("SELECT COUNT(*) FROM $tbl WHERE $colPeriod=:period", array( 'period' => $periodValue ));
        $mockConn
            ->expects($this->once())
            ->method('fetchOne')
            ->will($this->returnValue($FOUND));
        /**
         * Setup config and perform call.
         */
        Config::set($mockCfg);
        /** @var  $hndl Praxigento_Bonus_Service_Snapshot_Hndl_Db */
        $hndl = new Praxigento_Bonus_Service_Snapshot_Hndl_Db();
        $result = $hndl->isThereDownlinesSnapForPeriod($PERIOD);
        $this->assertEquals($PERIOD, $result);
    }

    public function test_isThereDownlinesSnapForPeriod_noData() {
        $PERIOD = '20150601';
        /**
         * Create mocks (direct order).
         */
        /* Config:: */
        $mockCfg = $this
            ->getMockBuilder('Praxigento_Bonus_Config')
            ->setMethods(array( 'tableName', 'connectionWrite' ))
            ->getMock();
        // $conn = $cfg->connectionWrite();
        $mockConn = $this
            ->getMockBuilder('Magento_Db_Adapter_Pdo_Mysql')
            ->disableOriginalConstructor()
            ->setMethods(array( 'fetchOne' ))
            ->getMock();
        $mockCfg
            ->expects($this->any())
            ->method('connectionWrite')
            ->will($this->returnValue($mockConn));
        // $tbl       = $rsrc->getTableName(Config::CFG_MODEL . '/' . Config::ENTITY_SNAP_DOWNLINE);
        $mockCfg
            ->expects($this->once())
            ->method('tableName')
            ->will($this->returnValue('prxgt_bonus_snap_downline'));
        // $conn      = $rsrc->getConnection('core_write');
        $mockConn = $this
            ->getMockBuilder('Magento_Db_Adapter_Pdo_Mysql')
            ->disableOriginalConstructor()
            ->setMethods(array( 'fetchOne' ))
            ->getMock();
        // $rs        = $conn->fetchOne("SELECT COUNT(*) FROM $tbl WHERE $colPeriod=:period", array( 'period' => $periodValue ));
        $mockConn
            ->expects($this->any())
            ->method('fetchOne')
            ->will($this->returnValue(0));
        /**
         * Setup config and perform call.
         */
        Config::set($mockCfg);
        /** @var  $hndl Praxigento_Bonus_Service_Snapshot_Hndl_Db */
        $hndl = new Praxigento_Bonus_Service_Snapshot_Hndl_Db();
        $result = $hndl->isThereDownlinesSnapForPeriod($PERIOD);
        $this->assertNull($result);
    }

    public function test_isThereDownlinesSnapForPeriod_smallestEquality() {
        $PERIOD = '201506';
        $PERIOD_EXPECTED = '20150630';
        $NOT_FOUND = "0";
        $FOUND = "1";
        /**
         * Create mocks (direct order).
         */
        /* Config:: */
        $mockCfg = $this
            ->getMockBuilder('Praxigento_Bonus_Config')
            ->setMethods(array( 'tableName', 'connectionWrite' ))
            ->getMock();
        // $conn = $cfg->connectionWrite();
        $mockConn = $this
            ->getMockBuilder('Magento_Db_Adapter_Pdo_Mysql')
            ->disableOriginalConstructor()
            ->setMethods(array( 'fetchOne' ))
            ->getMock();
        $mockCfg
            ->expects($this->any())
            ->method('connectionWrite')
            ->will($this->returnValue($mockConn));
        // $tbl = $cfg->tableName(Config::CFG_MODEL . '/' . Config::ENTITY_SNAP_DOWNLINE);
        $mockCfg
            ->expects($this->once())
            ->method('tableName')
            ->will($this->returnValue('prxgt_bonus_snap_downline'));
        // $rs        = $conn->fetchOne("SELECT COUNT(*) FROM $tbl WHERE $colPeriod=:period", array( 'period' => $periodValue ));
        $mockConn
            ->expects($this->at(0))
            ->method('fetchOne')
            ->will($this->returnValue($NOT_FOUND));
        // $rs       = $conn->fetchOne("SELECT COUNT(*) FROM $tbl WHERE $colPeriod=:period", array( 'period' => $smallest ));
        $mockConn
            ->expects($this->at(1))
            ->method('fetchOne')
            ->will($this->returnValue($FOUND));
        /**
         * Setup config and perform call.
         */
        Config::set($mockCfg);
        /** @var  $hndl Praxigento_Bonus_Service_Snapshot_Hndl_Db */
        $hndl = new Praxigento_Bonus_Service_Snapshot_Hndl_Db();
        $result = $hndl->isThereDownlinesSnapForPeriod($PERIOD);
        $this->assertEquals($PERIOD_EXPECTED, $result);
    }

    public function test_getLatestDownlineSnapBeforePeriod() {
        $PERIOD = '201506';
        $FOUND = "20140101";
        /**
         * Create mocks (direct order).
         */
        /* Config:: */
        $mockCfg = $this
            ->getMockBuilder('Praxigento_Bonus_Config')
            ->setMethods(array( 'tableName', 'connectionWrite' ))
            ->getMock();
        // $tbl       = $rsrc->getTableName(Config::CFG_MODEL . '/' . Config::ENTITY_SNAP_DOWNLINE);
        $mockCfg
            ->expects($this->once())
            ->method('tableName')
            ->will($this->returnValue('prxgt_bonus_snap_downline'));
        // $conn      = $rsrc->getConnection('core_write');
        $mockConn = $this
            ->getMockBuilder('Magento_Db_Adapter_Pdo_Mysql')
            ->disableOriginalConstructor()
            ->setMethods(array( 'fetchOne' ))
            ->getMock();
        $mockCfg
            ->expects($this->any())
            ->method('connectionWrite')
            ->will($this->returnValue($mockConn));
        // $result    = $conn->fetchOne(...)
        $mockConn
            ->expects($this->once())
            ->method('fetchOne')
            ->will($this->returnValue($FOUND));
        /**
         * Setup config and perform call.
         */
        Config::set($mockCfg);
        /** @var  $hndl Praxigento_Bonus_Service_Snapshot_Hndl_Db */
        $hndl = new Praxigento_Bonus_Service_Snapshot_Hndl_Db();
        $result = $hndl->getLatestDownlineSnapBeforePeriod($PERIOD);
        $this->assertEquals($FOUND, $result);
    }

}