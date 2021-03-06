<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Nmmlm_Core_Config as CoreConfig;
use Praxigento_Bonus_Config as Config;
use Praxigento_Bonus_Model_Own_Balance as Balance;
use Praxigento_Bonus_Model_Own_Log_Downline as LogDownline;
use Praxigento_Bonus_Model_Own_Operation as Operation;
use Praxigento_Bonus_Model_Own_Period as Period;
use Praxigento_Bonus_Model_Own_Transaction as Transaction;
use Praxigento_Bonus_Service_Period_Response_GetPeriodForPersonalBonus as GetPeriodForPersonalBonus;

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
/*
 *  __DIR__ returns absolute path if Magento module is mounted using symbolic link
 * we need to include __DIR__ . '/../../abstract.php'
 */
$dir = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));
require_once $dir . '/abstract.php';

class Praxigento_Shell extends Mage_Shell_Abstract {
    const DEFAULT_UPLINE = 100000001;
    const OPT_BONUS_PV = 'bonusPv';
    const OPT_CREATE = 'create';
    const OPT_EMULATE = 'emulate';
    const OPT_SANTEGRA_CREATE = 'santegra-create';
    private $_categoryElectronics;
    private $_fileNameCustomers;
    private $_fileNameOrders;
    private $_fileNamePvTransfers;
    private $_fileNameSantegraCustomers;
    /** @var Logger */
    private $_log;
    /**
     * Registry to store customer data with Sponsor (Upline) ID as a key.
     *
     *
     * @var array
     */
    private $_regUpline = array();

    public function __construct() {
        parent::__construct();
        $this->_log = Praxigento_Log_Logger::getLogger(__CLASS__);
        $this->_fileNameCustomers = dirname($_SERVER['SCRIPT_NAME']) . '/data_customers.csv';
        $this->_fileNameOrders = dirname($_SERVER['SCRIPT_NAME']) . '/data_orders.csv';
        $this->_fileNamePvTransfers = dirname($_SERVER['SCRIPT_NAME']) . '/data_pv_transfers.csv';
        $this->_fileNameSantegraCustomers = dirname($_SERVER['SCRIPT_NAME']) . '/santegra_customers.csv';
    }

    /**
     * Compare two RecordCustomer objects by Upline ID.
     *
     * <code>
     *      $result = array(array('value'=>'...', 'label'=>'...'), ...);
     *      usort($result, array('Praxigento_Ad_Shell', 'compareByUpline'));
     * </code>
     *
     * @param RecordCustomer $a
     * @param RecordCustomer $b
     *
     * @return int see PHP function usort()
     */
    public static function compareByUpline(RecordCustomer $a, RecordCustomer $b) {
        $aa = (int)$a->mlmUpline;
        $bb = (int)$b->mlmUpline;
        if($aa == $bb) {
            $result = 0;
        } else {
            return ($aa < $bb) ? -1 : 1;
        }
        return $result;
    }

    /**
     * Run script
     *
     */
    public function run() {
        $create = $this->getArg(self::OPT_CREATE);
        $santegraCreate = $this->getArg(self::OPT_SANTEGRA_CREATE);
        $emulate = $this->getArg(self::OPT_EMULATE);
        $bonusPv = $this->getArg(self::OPT_BONUS_PV);
        if($create || $santegraCreate || $emulate || $bonusPv) {
            if($create) {
                $this->_log->debug("Sample data generation is started.");
                $this->_createCatalogCategories();
                $this->_createProducts();
                $this->_createCustomers();
                $this->_createOrders();
                $this->_log->debug("Sample data generation is completed.");
            }
            if($santegraCreate) {
                $this->_log->debug("Santegra sample data generation is started.");
                $this->_createSantegraCustomers();
                $this->_log->debug("Santegra sample data generation is completed.");
            }
            if($emulate) {
                /* add fake data to bonus module */
                $this->_emulateOrderOperations();
                $this->_emulatePvTransferOperations();
            }
            if($bonusPv) {
                $this->_calcPvWriteOff();
                //                $this->_calcBonusPv();
            }
            echo 'Done.';
        } else {
            echo $this->usageHelp();
        }

    }

    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp() {
        return <<<USAGE
Usage:  php -f sample_data.php [options]

  --create              Create sample data.
  --santegra-create     Create sample data based on Santegra imported data (MOBI-77).
  --emulate             Emulate operations.
  --bonusPv             Calculate PV bonus.

USAGE;
    }

    /**
     * We should write off PV for every period.
     */
    private function _calcPvWriteOff() {
        $call = Config::get()->serviceCalculation();
        for($i = 0; $i < 10; $i++) {
            $call->calcPvWriteOff();
        }
    }

    private function _calcBonusPv() {
        /** @var  $helper Praxigento_Bonus_Helper_Data */
        $helper = Config::get()->helper();
        /** @var  $helperType Praxigento_Bonus_Helper_Type */
        $helperType = Config::get()->helperType();
        $periodCode = $helper->cfgPersonalBonusPeriod();
        $typePeriod = $helperType->getPeriod($periodCode);
        $typeCalc = $helperType->getCalc(Config::CALC_BONUS_PERSONAL);
        $typeOperPvInt = $helperType->getOper(Config::OPER_PV_INT);
        $typeOperPvOrder = $helperType->getOper(Config::OPER_ORDER_PV);
        $operIds = array( $typeOperPvInt->getId(), $typeOperPvOrder->getId() );
        /* get calculation period */
        $result = null;
        /** @var  $call Praxigento_Bonus_Service_Period_Call */
        $call = Config::get()->servicePeriod();
        /** @var  $req  Praxigento_Bonus_Service_Period_Request_GetPeriodForPersonalBonus */
        $req = $call->requestPeriodForPersonalBonus();
        $req->setBonusTypeId($typeCalc->getId());
        $req->setOperationTypeIds($operIds);
        $req->setPeriodCode($periodCode);
        $req->setPeriodTypeId($typeCalc->getId());
        /** @var  $resp Praxigento_Bonus_Service_Period_Response_GetPeriodForPersonalBonus */
        $resp = $call->getPeriodForPersonalBonus($req);
        if($resp->isSucceed()) {
            /** @var  $balance Praxigento_Bonus_Model_Own_Period */
            $period = Mage::getModel('prxgt_bonus_model/period');
            if($resp->isNewPeriod()) {
                /* insert new period into DB */
                $period->setType($typePeriod->getId());
                $period->setCalcTypeId($typeCalc->getId());
                $period->setValue($resp->getPeriodValue());
                $period->setState(Config::STATE_PERIOD_PROCESSING);
                $period->save();
                $this->_log->debug("New period '{$period->getValue()}' (#{$period->getId()}) is created to process personal bonus.");
            } else {
                $period->load($resp->getExistingPeriodId());
                $this->_log->debug("Existing period '{$period->getValue()}' (#{$period->getId()}) is used to process personal bonus.");
            }
            /* select all operation for period */
            $opers = $this->_getOperationsForPersonalBonus($operIds, $period->getValue(), $periodCode);
            1 + 1;
        } else {
            if($resp->getErrorCode() == GetPeriodForPersonalBonus::ERR_NOTHING_TO_DO) {
                $this->_log->warn("There are no periods/operations to calculate personal bonus.");
            } else {
                $this->_log->warn("Cannot get period to calculate personal bonus.");
            }
        }
    }

    private function _getOperationsForPersonalBonus($operIds = array(), $period, $periodCode) {
        /**
         *
         * SELECT
         *
         * FROM prxgt_bonus_operation pbo
         * LEFT OUTER JOIN prxgt_bonus_trnx pbt
         * ON pbo.id = pbt.operation_id
         * WHERE (
         * pbo.type_id = 1
         * OR pbo.type_id = 3
         * )
         * AND pbt.date_applied >= '2015-06-01 00:00:00'
         * AND pbt.date_applied <= '2015-06-01 23:59:59'
         */
        $collection = Mage::getModel('prxgt_bonus_model/operation')->getCollection();
        /* filter by operations types */
        $fields = array();
        $values = array();
        foreach($operIds as $one) {
            $fields[] = Operation::ATTR_TYPE_ID;
            $values[] = $one;
        }
        $collection->addFieldToFilter($fields, $values);
        $tableTrnx = $collection->getTable(Config::ENTITY_TRANSACTION);
        $collection->getSelect()->joinLeft(
            array( 'trnx' => $tableTrnx ),
            'main_table.id = trnx.operation_id',
            '*'
        );
        $fldDate = 'trnx.' . Transaction::ATTR_DATE_APPLIED;
        $from = Config::helperPeriod()->calcPeriodTsFrom($period, $periodCode);
        $to = Config::helperPeriod()->calcPeriodTsTo($period, $periodCode);
        $collection->addFieldToFilter($fldDate, array( 'gteq' => $from ));
        $collection->addFieldToFilter($fldDate, array( 'lteq' => $to ));
        $sql = $collection->getSelectSql(true);
        return $collection;
    }

    private function _getOperationsForPvWriteOff($logCalcId, $periodValue = null, $periodCode = null) {
        $result = array();
        $call = Config::get()->serviceOperations();
        $req = $call->requestGetOperationsForPvWriteOff();
        $req->setPeriodValue($periodValue);
        $req->setPeriodCode($periodCode);
        $req->setCalcTypeId($logCalcId);
        $resp = $call->getOperationsForPvWriteOff($req);
        if($resp->isSucceed()) {
            $result = $resp->getCollection();
        }
        return $result;
    }

    private function _createCatalogCategories() {
        /** @var  $allCats Mage_Catalog_Model_Resource_Category_Collection */
        $allCats = Mage::getModel('catalog/category')->getCollection();
        /** @var  $rootCat Mage_Catalog_Model_Category */
        $rootCat = $allCats->getFirstItem();
        /** @var  $subCats Mage_Catalog_Model_Resource_Category_Collection */
        $subCats = $rootCat->getChildrenCategories();
        $defaultCat = $subCats->getFirstItem();
        /** @var  $category Mage_Catalog_Model_Category */
        $category = Mage::getModel('catalog/category');
        $category->setName('Electronics');
        $category->setIsActive(true);
        $category->setPath($defaultCat->getPath());
        $category->save();
        $this->_categoryElectronics = $category;
        $this->_log->debug("'Electronics'category is added to catalog (default category)");

    }

    private function _createProducts() {
        $be = Mage::getModel('nmmlm_core_model/dict_catalog_product');
        //        $be->setBeId(100);
        $be->setEnabled(true);
        $be->setMageSku('sku1213');
        $be->setName(new Nmmlm_Core_Model_Dict_I18n_Text("product1"));
        $be->setDescription(new Nmmlm_Core_Model_Dict_I18n_Text('description'));

        $be->setMageCategoryIds(array( $this->_categoryElectronics->getId() ));

        $be->setPriceAdjusted(new Nmmlm_Core_Model_Dict_Catalog_Price(4.01, 'USD'));
        $be->setPriceRegular(new Nmmlm_Core_Model_Dict_Catalog_Price(6.32, 'USD'));
        $be->setPriceWholesale(new Nmmlm_Core_Model_Dict_Catalog_Price(3.16, 'USD'));
        $be->setPvWholesale(100);
        $be->setWeight(0.200);

        $be->getInventory()->setQty(1000, 1);
        $be->getInventory()->setPvActual(120, 1);
        $be->getInventory()->setPvWarehouse(130, 1);
        $be->getInventory()->setPriceWarehouse(new Nmmlm_Core_Model_Dict_Catalog_Price(4.24, 'USD'), 1);

        $sync = Mage::getModel('nmmlm_core_model/sync_mage_product');
        $sync->addOne($be);

        //        $prod = Mage::getModel('catalog/product');
        //        try {
        //            $prod
        //                ->setStoreId(1) //you can set data in store scope
        //                ->setWebsiteIds(array(1)) //website ID the product is assigned to, as an array
        //                ->setAttributeSetId(4)//ID of a attribute set named 'default' for type 'catalog_product'
        //                ->setTypeId('simple')//product type
        //                ->setCreatedAt(strtotime('now'))//product creation time
        //                ->setSku('smartphone')//SKU
        //                ->setName('test product21')//product name
        //                ->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
        //                ->setTaxClassId(1)//tax class (0 - none, 1 - default, 2 - taxable, 4 - shipping)
        //                ->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)//catalog and search visibility
        //                ->setPrice(11.22)//price in form 11.22
        //
        //                ->setDescription('This is a long description')
        //                ->setShortDescription('This is a short description')
        //                ->setStockData(array(
        //                        'use_config_manage_stock' => 0, //'Use config settings' checkbox
        //                        'manage_stock' => 1, //manage stock
        //                        'min_sale_qty' => 1, //Minimum Qty Allowed in Shopping Cart
        //                        'max_sale_qty' => 2, //Maximum Qty Allowed in Shopping Cart
        //                        'is_in_stock' => 1, //Stock Availability
        //                        'qty' => 999 //qty
        //                    )
        //                )
        //                ->setCategoryIds(array($this->_categoryElectronics->getId())); //assign product to categories
        //            $prod->save();
        //        } catch (Exception $e) {
        //            Mage::log($e->getMessage());
        //        }
    }

    private function _createCustomers() {
        $records = $this->_readDataCustomers($this->_fileNameCustomers);
        $count = sizeof($records);
        if($count) {
            $this->_log->debug("Total $count lines are read from file {$this->_fileNameCustomers}");
            /* create customers and generate MLM IDs */
            foreach($records as $one) {
                $this->_createCustomerEntry($one);
            }
            /* set up Upline and MLM Path */
            foreach($records as $one) {
                $this->_updateUpline($one);
            }
        }
    }

    private function _createSantegraCustomers() {
        $records = $this->_readDataSantegraCustomers($this->_fileNameSantegraCustomers);
        $count = sizeof($records);
        if($count) {
            /** @var  $rsrc Mage_Core_Model_Resource */
            /** $rsrc = Mage::getSingleton('core/resource'); */
            $rsrc = Config::get()->singleton('core/resource');
            /** @var  $conn Varien_Db_Adapter_Interface */
            $conn = $rsrc->getConnection('core_write');
            $conn->beginTransaction();
            try {
                $this->_log->debug("Total $count lines are read from file {$this->_fileNameCustomers}");
                /* insert data into 'customer_entity' table */
                $this->_dbInsertRecords($records, 'customer/entity');
                /* read all inserted data and compose array to lookup MageId by MLM ID */
                $entities = $this->_readCustomersEntries();
                $map = array();
                foreach($entities as $one) {
                    $map[ $one[ CoreConfig::ATTR_CUST_MLM_ID ] ] = $one;
                }
                /* cleanup downline snapshots */
                $this->_dbTruncate(Config::ENTITY_LOG_DOWNLINE);
                /* set up Upline and MLM Path */
                $logDownline = array();
                foreach($records as $one) {
                    $mlmId = $one[ CoreConfig::ATTR_CUST_MLM_ID ];
                    $mlmUpline = $one[ CoreConfig::ATTR_CUST_MLM_UPLINE ];
                    $date = $one[ CoreConfig::ATTR_CUST_MLM_DATE_ENROLLED ];
                    $mageId = $map[ $mlmId ]['entity_id'];
                    $mageUpline = $map[ $mlmUpline ]['entity_id'];
                    if(!$mageUpline) {
                        /* link customer to itself (root node in the downline tree) */
                        $mageUpline = $mageId;
                    }
                    $logDownline[] = array(
                        LogDownline::ATTR_CUSTOMER_ID  => $mageId,
                        LogDownline::ATTR_PARENT_ID    => $mageUpline,
                        LogDownline::ATTR_DATE_CHANGED => $date
                    );
                }
                $this->_dbInsertRecords($logDownline, Config::ENTITY_LOG_DOWNLINE);
                /* create downline snapshots for imported data */
                $call = Config::get()->serviceSnapshot();
                $req = $call->requestComposeDownlineSnapshot();
                $req->setPeriodValue('201506');
                $resp = $call->composeDownlineSnapshot($req);
                $req->setPeriodValue(Config::PERIOD_KEY_NOW);
                $resp = $call->composeDownlineSnapshot($req);
                $conn->commit();
            } catch(Exception $e) {
                $this->_log->error("Cannot create Santegra customers. Error: " . $e->getMessage());
                $conn->rollBack();
            }
        }
    }

    /**
     * Read file with data, parse and return array of Records.
     *
     * @param $path
     *
     * @return RecordCustomer[]
     */
    private function _readDataCustomers($path) {
        $result = array();
        /* registry to uniquelize emails */
        $emailReg = array();
        if(file_exists($path)) {
            $content = file($path);
            foreach($content as $one) {
                $data = explode(',', trim($one));
                $obj = new RecordCustomer();
                $obj->mlmId = $data[0];
                $obj->mlmUpline = $data[1];
                $obj->nameFirst = $data[2];
                $obj->nameLast = $data[3];
                $obj->groupId = $data[5];
                /**/
                $email = strtolower(trim($data[4]));
                if(isset($emailReg[ $email ])) {
                    $emailReg[ $email ]++;
                    $parts = explode('@', $email);
                    $email = $parts[0] . $emailReg[ $email ] . '@' . $parts[1];
                } else {
                    $emailReg[ $email ] = 0;
                }
                $obj->email = $email;
                $result[] = $obj;
            }
        } else {
            $this->_log->error("Cannot open file '$path'.");
        }
        //usort($result, array(__CLASS__, 'compareByUpline'));
        return $result;
    }

    /**
     * Read file with data, parse and return array of "customer_entity" record data for multiple insert.
     *
     * @param $path
     *
     * @return RecordSantegraCustomer[]
     */
    private function _readDataSantegraCustomers($path) {
        $result = array();
        if(file_exists($path)) {
            $now = '2015-06-01 12:01:02';
            $content = file($path);
            foreach($content as $one) {
                $data = explode(',', trim($one));
                $mlmId = $data[0];
                $uplineId = $data[1];
                /* compose columns */
                $record = array(
                    'entity_type_id'           => 1,
                    'attribute_set_id'         => 0,
                    'website_id'               => 1,
                    'email'                    => "$mlmId@praxigento.com",
                    'group_id'                 => 1,
                    'store_id'                 => 0,
                    'created_at'               => $now,
                    'updated_at'               => $now,
                    'is_active'                => 1,
                    'nmmlm_core_mlm_id'        => $mlmId,
                    'nmmlm_core_mlm_upline'    => $uplineId,
                    'nmmlm_core_mlm_enrolment' => $now
                );
                $result[] = $record;
            }
        } else {
            $this->_log->error("Cannot open file '$path'.");
        }
        return $result;
    }

    private function _createCustomerEntry(RecordCustomer $rec) {
        $nameFirst = $rec->nameFirst;
        $nameLast = $rec->nameLast;
        $email = $rec->email;
        /* save customer and update customer group */
        $customer = Mage::getModel('customer/customer');
        $customer->setData(Nmmlm_Core_Config::ATTR_CUST_DATE_CREATED, null);
        $customer->setData(Nmmlm_Core_Config::ATTR_CUST_NAME_FIRST, $nameFirst);
        $customer->setData(Nmmlm_Core_Config::ATTR_CUST_NAME_LAST, $nameLast);
        $customer->setData(Nmmlm_Core_Config::ATTR_CUST_EMAIL, $email);
        try {
            $customer->save();
            $this->_log->trace("New customer '$nameFirst $nameLast <$email>' is saved.");
            /* save new record into registry */
            $this->_regUpline[ $rec->mlmId ] = $customer->getData(Nmmlm_Core_Config::ATTR_CUST_MLM_ID);
        } catch(Exception $e) {
            $this->_log->error("Cannot save customer '$nameFirst $nameLast <$email>'.", $e);
        }
    }

    private function _dbInsertRecords($records, $modelName) {
        /** @var  $rsrc Mage_Core_Model_Resource */
        $rsrc = Config::get()->singleton('core/resource');
        /** @var  $conn Varien_Db_Adapter_Interface */
        $conn = $rsrc->getConnection('core_write');
        $tbl = $rsrc->getTableName($modelName);
        $total = $conn->insertMultiple($tbl, $records);
        $this->_log->debug("Total '$total' records are inserted into '$tbl' table.");
    }

    private function _dbTruncate($modelName) {
        /** @var  $rsrc Mage_Core_Model_Resource */
        $rsrc = Config::get()->singleton('core/resource');
        /** @var  $conn Varien_Db_Adapter_Interface */
        $conn = $rsrc->getConnection('core_write');
        $tbl = $rsrc->getTableName($modelName);
        $sql = "TRUNCATE $tbl";
        $total = $conn->query($sql);
        $this->_log->debug("Table '$tbl' is truncated.");
    }

    private function _readCustomersEntries() {
        /** @var  $rsrc Mage_Core_Model_Resource */
        $rsrc = Config::get()->singleton('core/resource');
        /** @var  $conn Varien_Db_Adapter_Interface */
        $conn = $rsrc->getConnection('core_write');
        $tbl = $rsrc->getTableName('customer/entity');
        $sql = "SELECT * FROM $tbl";
        $result = $conn->fetchAll($sql);
        return $result;
    }

    private function _updateUpline(RecordCustomer $rec) {
        $mlmId = $this->_regUpline[ $rec->mlmId ];
        $mlmUpline = isset($this->_regUpline[ $rec->mlmUpline ]) ?
            $this->_regUpline[ $rec->mlmUpline ] : self::DEFAULT_UPLINE;
        /* I can use "$groupId = $rec->groupId" but I prefer this code */
        $groupId = $rec->groupId;
        /* save customer and update customer group */
        $wrapper = new Nmmlm_Core_Wrapper_Customer();
        $wrapper->initByMlmId($mlmId);
        $wrapper->setData(Nmmlm_Core_Config::ATTR_CUST_MLM_UPLINE, $mlmUpline);
        $wrapper->setData(Nmmlm_Core_Config::ATTR_CUST_MLM_PATH, null);
        $wrapper->setData(Nmmlm_Core_Config::ATTR_CUST_GROUP_ID, $groupId);
        $wrapper->setData('website_id', 1);
        $wrapper->save();
        $this->_log->trace("New group '$groupId' is set for customer '{$rec->email}'.");

    }

    /**
     * Create one order per customer.
     */
    private function _createOrders() {
        $records = $this->_readDataOrders($this->_fileNameOrders);
        /** @var  $allProducts Innoexts_WarehousePlus_Model_Mysql4_Catalog_Product_Collection */
        $allProducts = Mage::getModel('catalog/product')->getCollection();
        /** @var  $product Mage_Catalog_Model_Product */
        $product = $allProducts->getFirstItem();
        foreach($records as $one) {
            $this->_createOrderForCustomer($one, $product);
        }

    }

    /**
     * Read file with data, parse and return array of Records.
     *
     * @param $path
     *
     * @return RecordCustomer[]
     */
    private function _readDataOrders($path) {
        $result = array();
        if(file_exists($path)) {
            $content = file($path);
            foreach($content as $one) {
                $data = explode(',', trim($one));
                $obj = new RecordOrder();
                $obj->mlmId = $data[0];
                $obj->orderDate = $data[1];
                $obj->orderAmount = $data[2];
                $obj->orderPv = $data[3];
                $result[] = $obj;
            }
        } else {
            $this->_log->error("Cannot open file '$path'.");
        }
        //usort($result, array(__CLASS__, 'compareByUpline'));
        return $result;
    }

    /**
     * Read file with data, parse and return array of Records.
     *
     * @param $path
     *
     * @return RecordPvTransfer[]
     */
    private function _readDataPvTransfers($path) {
        $result = array();
        if(file_exists($path)) {
            $content = file($path);
            foreach($content as $one) {
                $data = explode(',', trim($one));
                $obj = new RecordPvTransfer();
                $obj->date = $data[0];
                $obj->mlmIdFrom = $data[1];
                $obj->mlmIdTo = $data[2];
                $obj->pv = $data[3];
                $result[] = $obj;
            }
        } else {
            $this->_log->error("Cannot open file '$path'.");
        }
        return $result;
    }

    private function _createOrderForCustomer(
        RecordOrder $record,
        Mage_Catalog_Model_Product $product
    ) {
        $customer = Nmmlm_Core_Util::findCustomerByMlmId($record->mlmId);
        /** @var  $quote Mage_Sales_Model_Quote */
        $quote = Mage::getModel('sales/quote')->setStoreId(Mage::app()->getStore('default')->getId());
        $quote->assignCustomer($customer);
        $buyInfo = array( 'qty' => 1 );
        $quote->addProduct($product, new Varien_Object($buyInfo));

        $addressData = array(
            'firstname'  => 'Test',
            'lastname'   => 'Test',
            'street'     => 'Sample Street 10',
            'city'       => 'Somewhere',
            'postcode'   => '123456',
            'telephone'  => '123456',
            'country_id' => 'US',
            'region_id'  => 12, // id from directory_country_region table
        );
        $quote->getBillingAddress()->addData($addressData);
        $shippingAddress = $quote->getShippingAddress()->addData($addressData);
        $shippingAddress->setCollectShippingRates(true)->collectShippingRates()
                        ->setShippingMethod('flatrate_flatrate')
                        ->setPaymentMethod('checkmo');

        $quote->getPayment()->importData(array( 'method' => 'checkmo' ));

        $quote->collectTotals()->save();

        $service = Mage::getModel('sales/service_quote', $quote);
        $service->submitAll();

        /** @var  $order Mage_Sales_Model_Order */
        $order = $service->getOrder();

        $order->setCreatedAt($record->orderDate);
        $order->setUpdatedAt($record->orderDate);
        $order->setBaseGrandTotal($record->orderAmount);
        $order->setGrandTotal($record->orderAmount);
        $order->setData(Nmmlm_Core_Config::ATTR_COMMON_PV_SUBTOTAL, $record->orderPv);
        $order->setData(Nmmlm_Core_Config::ATTR_COMMON_PV_TOTAL, $record->orderPv);

        $order->save();
        $this->_log->debug("Order #" . $order->getIncrementId() . " is created.");

    }

    private function _emulatePvTransferOperations() {
        /** @var  $helperType Praxigento_Bonus_Helper_Type */
        $helperType = Config::get()->helperType();
        /** @var  $opType Praxigento_Bonus_Model_Own_Type_Operation */
        $opType = $helperType->getOper(Praxigento_Bonus_Config::OPER_PV_INT);
        /** @var  $assetType Praxigento_Bonus_Model_Own_Type_Asset */
        $assetType = $helperType->getAsset(Praxigento_Bonus_Config::ASSET_PV);

        $records = $this->_readDataPvTransfers($this->_fileNamePvTransfers);
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        foreach($records as $one) {
            $date = $one->date;
            $customerFrom = Nmmlm_Core_Util::findCustomerByMlmId($one->mlmIdFrom);
            $customerTo = Nmmlm_Core_Util::findCustomerByMlmId($one->mlmIdTo);
            $pv = $one->pv;
            try {
                $connection->beginTransaction();
                $accountFrom = $this->_getAssetAccountByCustomerId($assetType->getId(), $customerFrom->getId());
                $accountTo = $this->_getAssetAccountByCustomerId($assetType->getId(), $customerTo->getId());
                /* create operation */
                $operation = Mage::getModel('prxgt_bonus_model/operation');
                $operation->setDatePerformed($date);
                $operation->setTypeId($opType->getId());
                $operation->save();
                $operationId = $operation->getId();
                /* don't create transactions for empty operations */
                if($pv != 0) {
                    /* create transaction */
                    $this->_createTransaction($operationId, $accountFrom->getId(), $accountTo->getId(), $pv, $date);
                }
                $connection->commit();
            } catch(Exception $e) {
                $connection->rollback();
            }
        }
    }

    private function _getStoreCustomerId() {
        $result = Config::get()->helperAccount()->getAccountantMageId();
        return $result;
    }

    /**
     * Save operations and transactions and update customer balances according to sales orders.
     */
    private function _emulateOrderOperations() {
        /** @var  $helperType Praxigento_Bonus_Helper_Type */
        $helperType = Config::get()->helperType();
        $opType = $helperType->getOper(Praxigento_Bonus_Config::OPER_ORDER_PV);
        $assetType = $helperType->getAsset(Praxigento_Bonus_Config::ASSET_PV);
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $allOrders = Mage::getModel('sales/order')->getCollection();
        foreach($allOrders as $one) {
            /* create operation and transaction to customer account */
            $customerId = $one->getCustomerId();
            $pv = $one->getData(Nmmlm_Core_Config::ATTR_COMMON_PV_TOTAL);
            $date = $one->getCreatedAtDate();
            try {
                $connection->beginTransaction();
                $accountStore = $this->_getAssetAccountByCustomerId($assetType->getId(), $this->_getStoreCustomerId());
                $accountStoreId = $accountStore->getId();
                $accountCustomer = $this->_getAssetAccountByCustomerId($assetType->getId(), $customerId);
                $accountCustomerId = $accountCustomer->getId();
                /* create operation */
                $operation = Mage::getModel('prxgt_bonus_model/operation');
                $operation->setDatePerformed($date);
                $operation->setTypeId($opType->getId());
                $operation->save();
                $operationId = $operation->getId();
                /* don't create transactions for empty operations */
                if($pv != 0) {
                    /* create transaction */
                    $this->_createTransaction($operationId, $accountStoreId, $accountCustomerId, $pv, $date);
                }
                $connection->commit();
            } catch(Exception $e) {
                $connection->rollback();
            }
        }
    }

    /**
     * Select customer account by asset and customer ids or create new one.
     *
     * @param $assetId
     * @param $customerId
     *
     * @return Praxigento_Bonus_Model_Own_Account|Varien_Object
     */
    private function _getAssetAccountByCustomerId($assetId, $customerId) {
        /*  */
        /** @var  $account Praxigento_Bonus_Resource_Own_Account_Collection */
        $accountCollection = Mage::getModel('prxgt_bonus_model/account')->getCollection();
        $accountCollection->addFieldToFilter(Praxigento_Bonus_Model_Own_Account::ATTR_CUSTOMER_ID, $customerId);
        $accountCollection->addFieldToFilter(Praxigento_Bonus_Model_Own_Account::ATTR_ASSET_ID, $assetId);
        /** @var  $account Praxigento_Bonus_Model_Own_Account */
        $result = Mage::getModel('prxgt_bonus_model/account');
        if($accountCollection->getSize()) {
            $result = $accountCollection->getFirstItem();
        } else {
            /* create new account */
            $result->setCustomerId($customerId);
            $result->setAssetId($assetId);
            $result->save();
        }
        return $result;
    }

    private function _updateBalance($accountId, $value, $period = Praxigento_Bonus_Config::PERIOD_KEY_NOW) {
        /** @var  $balanceCollection Praxigento_Bonus_Resource_Own_Balance_Collection */
        $balanceCollection = Mage::getModel('prxgt_bonus_model/balance')->getCollection();
        $balanceCollection->addFieldToFilter(Balance::ATTR_ACCOUNT_ID, $accountId);
        $balanceCollection->addFieldToFilter(Balance::ATTR_PERIOD, $period);
        /** @var  $balance Praxigento_Bonus_Model_Own_Balance */
        $balance = Mage::getModel('prxgt_bonus_model/balance');
        if($balanceCollection->getSize()) {
            $balance = $balanceCollection->getFirstItem();
        } else {
            /* create new balance record for NOW  */
            $balance->setAccountId($accountId);
            $balance->setPeriod(Praxigento_Bonus_Config::PERIOD_KEY_NOW);
            $balance->save();
        }
        $val = $balance->getValue() + $value;
        $balance->setValue($val);
        $balance->save();
    }

    private function _createTransaction($operationId, $debitId, $creditId, $value, $date) {
        /* create transaction */
        /** @var  $trnx Praxigento_Bonus_Model_Own_Transaction */
        $trnx = Mage::getModel('prxgt_bonus_model/transaction');
        $trnx->setOperationId($operationId);
        $trnx->setDateApplied($date);
        $trnx->setDebitAccId($debitId);
        $trnx->setCreditAccId($creditId);
        $trnx->setValue($value);
        $trnx->save();
        /* update balances */
        $this->_updateBalance($debitId, -$value);
        $this->_updateBalance($creditId, $value);
    }
}

/**
 * Class RecordCustomer Bean to group data from data file with customer info.
 */
class RecordCustomer {
    public $email;
    public $groupId;
    public $mlmId;
    public $mlmUpline;
    public $nameFirst;
    public $nameLast;
}

/**
 * Class RecordSantegraCustomer Bean to group data from data file with Santegra customers info.
 */
class RecordSantegraCustomer {
    public $mlmId;
    public $mlmUpline;
    public $bonusPlan;
}

/**
 * Class RecordOrder Bean to group data from data file with orders info.
 */
class RecordOrder {
    public $mlmId;
    public $orderAmount;
    public $orderDate;
    public $orderPv;
}

/**
 * Class RecordPvTransfer Bean to group data from data file with internal PV transfers info.
 */
class RecordPvTransfer {
    public $date;
    public $mlmIdFrom;
    public $mlmIdTo;
    public $pv;
}

/* prevent Notice: A session had already been started */
session_start();
$shell = new Praxigento_Shell();
$shell->run();
