<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;
use Praxigento_Bonus_Model_Own_Log_Calc as LogCalc;
use Praxigento_Bonus_Model_Own_Snap_Downline as SnapDownline;
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

    public function test_getDownlineLogs() {
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
            ->setMethods(array( 'fetchAll' ))
            ->getMock();
        $mockCfg
            ->expects($this->any())
            ->method('connectionWrite')
            ->will($this->returnValue($mockConn));
        // $tbl = $cfg->tableName(Config::TABLE_LOG_DOWNLINE);
        $mockCfg
            ->expects($this->once())
            ->method('tableName')
            ->with(Config::ENTITY_LOG_DOWNLINE)
            ->will($this->returnValue('prxgt_bonus_log_downline'));
        // $result = $conn->fetchAll(...)
        $mockConn
            ->expects($this->once())
            ->method('fetchAll')
            ->will($this->returnValue(array()));
        /**
         * Setup config and perform call.
         */
        Config::set($mockCfg);
        /** @var  $hndl Praxigento_Bonus_Service_Snapshot_Hndl_Db */
        $hndl = new Praxigento_Bonus_Service_Snapshot_Hndl_Db();
        $result = $hndl->getDownlineLogs('from', 'to');
        $this->assertTrue(is_array($result));
    }

    public function test_getDownlineSnapEntry() {
        $CUST_ID = 1024;
        /**
         * Create mocks (direct order).
         */
        /* Config:: */
        $mockCfg = $this
            ->getMockBuilder('Praxigento_Bonus_Config')
            ->setMethods(array( 'tableName', 'connectionWrite', 'modelSnapDownline' ))
            ->getMock();
        // $conn = $cfg->connectionWrite();
        $mockConn = $this
            ->getMockBuilder('Magento_Db_Adapter_Pdo_Mysql')
            ->disableOriginalConstructor()
            ->setMethods(array( 'select', 'fetchRow' ))
            ->getMock();
        $mockCfg
            ->expects($this->any())
            ->method('connectionWrite')
            ->will($this->returnValue($mockConn));
        // $result = $cfg->modelSnapDownline();
        //        $mockResult = $this
        //            ->getMockBuilder('Praxigento_Bonus_Model_Own_Snap_Downline')
        //            ->disableOriginalConstructor()
        //            //            ->setMethods(array( 'setData' ))
        //            ->getMock();
        $mockResult = new Praxigento_Bonus_Model_Own_Snap_Downline();
        $mockCfg
            ->expects($this->any())
            ->method('modelSnapDownline')
            ->will($this->returnValue($mockResult));
        // $tblSnap = $cfg->tableName($eSnap, $asSnap);
        $mockCfg
            ->expects($this->any())
            ->method('tableName')
            ->with(Config::ENTITY_SNAP_DOWNLINE)
            ->will($this->returnValue('prxgt_bonus_snap_downline'));
        // $query = $conn->select();
        $mockQuery = $this
            ->getMockBuilder('Varien_Db_Select')
            ->disableOriginalConstructor()
            ->getMock();
        $mockConn
            ->expects($this->any())
            ->method('select')
            ->will($this->returnValue($mockQuery));
        // $result = $conn->fetchAll(...)
        $mockConn
            ->expects($this->at(1))
            ->method('fetchRow')
            // customer_id - is the ID field for the "customer/entity".
            ->will($this->returnValue(array( 'customer_id' => $CUST_ID )));
        $mockConn
            ->expects($this->at(2))
            ->method('fetchRow')
            ->will($this->returnValue(false));
        /**
         * Setup config and perform call.
         */
        Config::set($mockCfg);
        /** @var  $hndl Praxigento_Bonus_Service_Snapshot_Hndl_Db */
        $hndl = new Praxigento_Bonus_Service_Snapshot_Hndl_Db();
        /* first run: there is data in DB */
        $result = $hndl->getDownlineSnapEntry($CUST_ID);
        $this->assertTrue($result instanceof Praxigento_Bonus_Model_Own_Snap_Downline);
        $this->assertEquals($CUST_ID, $result->getId());
        /* first run: there is no data in DB */
        $result = $hndl->getDownlineSnapEntry($CUST_ID);
    }

    public function test_getDownlineSnapForPeriod() {
        $PERIOD_MONTH = '201506';
        $PERIOD_DAY = '20150630';
        /**
         * Create mocks (direct order).
         */
        /* Config:: */
        $mockCfg = $this
            ->getMockBuilder('Praxigento_Bonus_Config')
            ->setMethods(array( 'tableName', 'connectionWrite', 'helperPeriod' ))
            ->getMock();
        // $conn = $cfg->connectionWrite();
        $mockConn = $this
            ->getMockBuilder('Magento_Db_Adapter_Pdo_Mysql')
            ->disableOriginalConstructor()
            ->setMethods(array( 'fetchAll' ))
            ->getMock();
        $mockCfg
            ->expects($this->any())
            ->method('connectionWrite')
            ->will($this->returnValue($mockConn));
        // $hlp = $cfg->helperPeriod();
        $mockHelperPeriod = $this
            ->getMockBuilder('Praxigento_Bonus_Helper_Period')
            ->disableOriginalConstructor()
            ->setMethods(array( 'calcPeriodSmallest' ))
            ->getMock();
        $mockCfg
            ->expects($this->once())
            ->method('helperPeriod')
            ->will($this->returnValue($mockHelperPeriod));
        // $smallest = $hlp->calcPeriodSmallest($periodValue);
        $mockHelperPeriod
            ->expects($this->once())
            ->method('calcPeriodSmallest')
            ->with($this->equalTo($PERIOD_MONTH))
            ->will($this->returnValue($PERIOD_DAY));
        // $tbl = $cfg->tableName(Config::ENTITY_SNAP_DOWNLINE);
        $mockCfg
            ->expects($this->once())
            ->method('tableName')
            ->with(Config::ENTITY_SNAP_DOWNLINE)
            ->will($this->returnValue('prxgt_bonus_snap_downline'));
        // $result = $conn->fetchAll(...)
        $mockConn
            ->expects($this->once())
            ->method('fetchAll')
            ->will($this->returnValue(array()));
        /**
         * Setup config and perform call.
         */
        Config::set($mockCfg);
        /** @var  $hndl Praxigento_Bonus_Service_Snapshot_Hndl_Db */
        $hndl = new Praxigento_Bonus_Service_Snapshot_Hndl_Db();
        $result = $hndl->getDownlineSnapForPeriod($PERIOD_MONTH);
        $this->assertTrue(is_array($result));
    }

    public function test_getFirstDownlineLogBeforePeriod() {
        $PERIOD_MONTH = '201506';
        $PERIOD_SMALLEST = '20150630';
        $TIMESTAMP_TO = '2015-06-30 06:59:59';
        $TIMESTAMP_RESULT = '2015-06-15 06:10:20';
        /**
         * Create mocks (direct order).
         */
        /* Config:: */
        $mockCfg = $this
            ->getMockBuilder('Praxigento_Bonus_Config')
            ->setMethods(array( 'tableName', 'connectionWrite', 'helperPeriod' ))
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
        // $hlpPeriod = $cfg->helperPeriod();
        $mockHelperPeriod = $this
            ->getMockBuilder('Praxigento_Bonus_Helper_Period')
            ->disableOriginalConstructor()
            ->setMethods(array( 'calcPeriodSmallest', 'calcPeriodTsTo' ))
            ->getMock();
        $mockCfg
            ->expects($this->once())
            ->method('helperPeriod')
            ->will($this->returnValue($mockHelperPeriod));
        // $periodDaily = $hlpPeriod->calcPeriodSmallest($periodValue);
        $mockHelperPeriod
            ->expects($this->once())
            ->method('calcPeriodSmallest')
            ->with($this->equalTo($PERIOD_MONTH))
            ->will($this->returnValue($PERIOD_SMALLEST));
        // $to = $hlpPeriod->calcPeriodTsTo($periodDaily, Config::PERIOD_DAY);
        $mockHelperPeriod
            ->expects($this->once())
            ->method('calcPeriodTsTo')
            ->will($this->returnValue($TIMESTAMP_TO));
        // $tbl = $cfg->tableName(Config::ENTITY_LOG_DOWNLINE);
        $mockCfg
            ->expects($this->once())
            ->method('tableName')
            ->with(Config::ENTITY_LOG_DOWNLINE)
            ->will($this->returnValue('prxgt_bonus_log_downline'));
        // $result = $conn->fetchOne(...)
        $mockConn
            ->expects($this->once())
            ->method('fetchOne')
            ->will($this->returnValue($TIMESTAMP_RESULT));
        /**
         * Setup config and perform call.
         */
        Config::set($mockCfg);
        /** @var  $hndl Praxigento_Bonus_Service_Snapshot_Hndl_Db */
        $hndl = new Praxigento_Bonus_Service_Snapshot_Hndl_Db();
        $result = $hndl->getFirstDownlineLogBeforePeriod($PERIOD_MONTH);
        $this->assertEquals($TIMESTAMP_RESULT, $result);
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
        // $tbl       = $rsrc->getTableName(Config::ENTITY_SNAP_DOWNLINE);
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
        // $tbl       = $rsrc->getTableName(Config::ENTITY_SNAP_DOWNLINE);
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
        // $tbl       = $rsrc->getTableName(Config::ENTITY_SNAP_DOWNLINE);
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

    public function test_saveDownlineSnaps() {
        $INSERTED = 1024;
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
            ->setMethods(array( 'insertMultiple' ))
            ->getMock();
        $mockCfg
            ->expects($this->any())
            ->method('connectionWrite')
            ->will($this->returnValue($mockConn));
        // $tbl = $cfg->tableName(Config::TABLE_LOG_DOWNLINE);
        $mockCfg
            ->expects($this->once())
            ->method('tableName')
            ->with(Config::ENTITY_SNAP_DOWNLINE)
            ->will($this->returnValue('prxgt_bonus_snap_downline'));
        // $result = $conn->fetchAll(...)
        $mockConn
            ->expects($this->once())
            ->method('insertMultiple')
            ->will($this->returnValue($INSERTED));
        /**
         * Setup config and perform call.
         */
        Config::set($mockCfg);
        /** @var  $hndl Praxigento_Bonus_Service_Snapshot_Hndl_Db */
        $hndl = new Praxigento_Bonus_Service_Snapshot_Hndl_Db();
        $result = $hndl->saveDownlineSnaps(array());
        $this->assertEquals($INSERTED, $result);
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
        // $tbl = $cfg->tableName(Config::ENTITY_SNAP_DOWNLINE);
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

    public function test_updateDownlineSnapChildren() {
        $PATH_OLD = '/some/old/path/';
        $PATH_OLD_DOWN = '/some/old/path/downline/';
        $PATH_NEW = '/some/new/path/';
        $DEPTH = 8;
        $CUST_ID = 32;
        $PERIOD = Config::PERIOD_KEY_NOW;
        $DEPTH_DELTA = 16;
        $UPDATED = 1;
        /**
         * Create mocks (direct order).
         */
        // $cfg = Config::get();
        $mockCfg = $this
            ->getMockBuilder('Praxigento_Bonus_Config')
            ->setMethods(array( 'tableName', 'connectionWrite' ))
            ->getMock();
        // $conn = $cfg->connectionWrite();
        $mockConn = $this
            ->getMockBuilder('Magento_Db_Adapter_Pdo_Mysql')
            ->disableOriginalConstructor()
            ->setMethods(array( 'select', 'fetchAll', 'quoteInto', 'update' ))
            ->getMock();
        $mockCfg
            ->expects($this->any())
            ->method('connectionWrite')
            ->will($this->returnValue($mockConn));
        // $query = $conn->select();
        $mockQuery = $this
            ->getMockBuilder('Varien_Db_Select')
            ->disableOriginalConstructor()
            ->getMock();
        $mockConn
            ->expects($this->any())
            ->method('select')
            ->will($this->returnValue($mockQuery));
        // $tblSnapDwnl = $cfg->tableName(Config::ENTITY_SNAP_DOWNLINE);
        $mockCfg
            ->expects($this->once())
            ->method('tableName')
            ->with(Config::ENTITY_SNAP_DOWNLINE)
            ->will($this->returnValue('prxgt_bonus_snap_downline'));
        // $all = $conn->fetchAll($query, array( 'path' => $pathOld . '%' ));
        $mockConn
            ->expects($this->once())
            ->method('fetchAll')
            ->will($this->returnValue(array(
                array(
                    SnapDownline::ATTR_PATH        => $PATH_OLD_DOWN,
                    SnapDownline::ATTR_DEPTH       => $DEPTH,
                    SnapDownline::ATTR_CUSTOMER_ID => $CUST_ID,
                    SnapDownline::ATTR_PERIOD      => $PERIOD
                )
            )));
        // $whereCust = $conn->quoteInto(SnapDownline::ATTR_CUSTOMER_ID . '=?', $custId);
        $mockConn
            ->expects($this->at(2))
            ->method('quoteInto')
            ->with(
                $this->equalTo(SnapDownline::ATTR_CUSTOMER_ID . '=?'),
                $this->equalTo($CUST_ID)
            )
            ->will($this->returnValue(SnapDownline::ATTR_CUSTOMER_ID . '=' . $CUST_ID));
        // $wherePeriod = $conn->quoteInto(SnapDownline::ATTR_PERIOD . '=?', $period);
        $mockConn
            ->expects($this->at(3))
            ->method('quoteInto')
            ->with(
                $this->equalTo(SnapDownline::ATTR_PERIOD . '=?'),
                $this->equalTo($PERIOD)
            )
            ->will($this->returnValue(SnapDownline::ATTR_PERIOD . "='$PERIOD'"));
        // $conn->update($tblSnapDwnl, $bind, $where);
        $mockConn
            ->expects($this->once())
            ->method('update')
            ->will($this->returnValue($UPDATED));
        /**
         * Setup config and perform call.
         */
        Config::set($mockCfg);
        /** @var  $hndl Praxigento_Bonus_Service_Snapshot_Hndl_Db */
        $hndl = new Praxigento_Bonus_Service_Snapshot_Hndl_Db();
        $result = $hndl->updateDownlineSnapChildren($PATH_OLD, $PATH_NEW, $DEPTH_DELTA, $PERIOD);
        $this->assertEquals($UPDATED, $result);
    }

    public function test_updateDownlineSnapParent() {
        $CUST_ID = 512;
        $PERIOD_VALUE = '20150630';
        $PARENT_ID_NEW = 256;
        $PATH_NEW = '/2/4/8/16/32/64/128/256/';
        $DEPTH_NEW = 9;
        $UPDATED = 1;
        /**
         * Create mocks (direct order).
         */
        // $cfg = Config::get();
        $mockCfg = $this
            ->getMockBuilder('Praxigento_Bonus_Config')
            ->setMethods(array( 'tableName', 'connectionWrite' ))
            ->getMock();
        // $conn = $cfg->connectionWrite();
        $mockConn = $this
            ->getMockBuilder('Magento_Db_Adapter_Pdo_Mysql')
            ->disableOriginalConstructor()
            ->setMethods(array( 'quoteInto', 'update' ))
            ->getMock();
        $mockCfg
            ->expects($this->any())
            ->method('connectionWrite')
            ->will($this->returnValue($mockConn));
        // $tblSnapDwnl = $cfg->tableName(Config::ENTITY_SNAP_DOWNLINE);
        $mockCfg
            ->expects($this->once())
            ->method('tableName')
            ->with(Config::ENTITY_SNAP_DOWNLINE)
            ->will($this->returnValue('prxgt_bonus_snap_downline'));
        // $whereCust = $conn->quoteInto(SnapDownline::ATTR_CUSTOMER_ID . '=?', $custId);
        $mockConn
            ->expects($this->at(0))
            ->method('quoteInto')
            ->with(
                $this->equalTo(SnapDownline::ATTR_CUSTOMER_ID . '=?'),
                $this->equalTo($CUST_ID)
            )
            ->will($this->returnValue(SnapDownline::ATTR_CUSTOMER_ID . '=' . $CUST_ID));
        // $wherePeriod = $conn->quoteInto(SnapDownline::ATTR_PERIOD . '=?', $period);
        $mockConn
            ->expects($this->at(1))
            ->method('quoteInto')
            ->with(
                $this->equalTo(SnapDownline::ATTR_PERIOD . '=?'),
                $this->equalTo($PERIOD_VALUE)
            )
            ->will($this->returnValue(SnapDownline::ATTR_PERIOD . '=' . $PERIOD_VALUE));
        // $result = $conn->update($tblSnapDwnl, $bind, $where);
        $mockConn
            ->expects($this->once())
            ->method('update')
            ->will($this->returnValue($UPDATED));
        /**
         * Setup config and perform call.
         */
        Config::set($mockCfg);
        /** @var  $hndl Praxigento_Bonus_Service_Snapshot_Hndl_Db */
        $hndl = new Praxigento_Bonus_Service_Snapshot_Hndl_Db();
        $result = $hndl->updateDownlineSnapParent($CUST_ID, $PERIOD_VALUE, $PARENT_ID_NEW, $PATH_NEW, $DEPTH_NEW);
        $this->assertEquals($UPDATED, $result);
    }

}