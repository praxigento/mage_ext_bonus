<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;
use Praxigento_Bonus_Model_Own_Balance as Balance;
use Praxigento_Bonus_Model_Own_Operation as Operation;
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

class Praxigento_Shell extends Mage_Shell_Abstract
{
    const DEFAULT_UPLINE = 100000001;
    const OPT_CREATE = 'create';
    const OPT_EMULATE = 'emulate';
    const OPT_BONUS_PV = 'bonusPv';
    /** @var Logger */
    private $_log;
    private $_fileNameCustomers;
    private $_fileNameOrders;
    private $_fileNamePvTransfers;
    /**
     * Registry to store customer data with Sponsor (Upline) ID as a key.
     *
     *
     * @var array
     */
    private $_regUpline = array();
    private $_categoryElectronics;
    /** @var  array of asset types; 'code' is the key. */
    private $_cacheAssetTypes;
    /** @var  array of bonus types; 'code' is the key. */
    private $_cacheBonusTypes;
    /** @var  array of operation types; 'code' is the key. */
    private $_cacheOperationTypes;
    /** @var  array of bonus calculation types; 'code' is the key. */
    private $_cachePeriodTypes;

    public function __construct()
    {
        parent::__construct();
        $this->_log = Praxigento_Log_Logger::getLogger(__CLASS__);
        $this->_fileNameCustomers = dirname($_SERVER['SCRIPT_NAME']) . '/data_customers.csv';
        $this->_fileNameOrders = dirname($_SERVER['SCRIPT_NAME']) . '/data_orders.csv';
        $this->_fileNamePvTransfers = dirname($_SERVER['SCRIPT_NAME']) . '/data_pv_transfers.csv';
    }

    /**
     * Run script
     *
     */
    public function run()
    {
        $create = $this->getArg(self::OPT_CREATE);
        $emulate = $this->getArg(self::OPT_EMULATE);
        $bonusPv = $this->getArg(self::OPT_BONUS_PV);
        if ($create || $emulate || $bonusPv) {
            if ($create) {
                $this->_log->debug("Sample data generation is started.");
                $this->_createCatalogCategories();
                $this->_createProducts();
                $this->_createCustomers();
                $this->_createOrders();
                $this->_log->debug("Sample data generation is completed.");
            }
            if ($emulate) {
                /* add fake data to bonus module */
                $this->_emulateOrderOperations();
                $this->_emulatePvTransferOperations();
            }
            if ($bonusPv) {
                $this->_calcPvPeriodsWriteOff();
//                $this->_calcBonusPv();
            }
            echo 'Done.';
        } else {
            echo $this->usageHelp();
        }

    }

    /**
     * We should write off PV for every period.
     */
    private function _calcPvPeriodsWriteOff()
    {
        /** @var  $helper Praxigento_Bonus_Helper_Data */
        $helper = Config::helper();
        $periodCode = $helper->cfgPersonalBonusPeriod();
        $typePeriod = $this->_getTypePeriod($periodCode);
        $typeCalc = $this->_getTypeCalc(Config::CALC_PV_WRITE_OFF);
        $typeOperPvInt = $this->_getTypeOperation(Config::OPER_PV_INT);
        $typeOperPvOrder = $this->_getTypeOperation(Config::OPER_ORDER_PV);
        $operIds = array($typeOperPvInt->getId(), $typeOperPvOrder->getId());
        /* get calculation period */
        $result = null;
        /** @var  $req  Praxigento_Bonus_Service_Period_Request_GetPeriodForPvWriteOff */
        $req = Mage::getModel(Config::CFG_SERVICE . '/period_request_getPeriodForPvWriteOff');
        $req->setCalcTypeId($typeCalc->getId());
        $req->setOperationTypeIds($operIds);
        $req->setPeriodCode($periodCode);
        $req->setPeriodTypeId($typePeriod->getId());
        /** @var  $call Praxigento_Bonus_Service_Period_Call */
        $call = Mage::getModel(Config::CFG_SERVICE . '/period_call');
        /** @var  $resp Praxigento_Bonus_Service_Period_Response_GetPeriodForPersonalBonus */
        $resp = $call->getPeriodForPersonalBonus($req);
        if ($resp->isSucceed()) {
            /** @var  $balance Praxigento_Bonus_Model_Own_Period */
            $period = Mage::getModel('prxgt_bonus_model/period');
            if ($resp->isNewPeriod()) {
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
            if ($resp->getErrorCode() == GetPeriodForPersonalBonus::ERR_NOTHING_TO_DO) {
                $this->_log->warn("There are no periods/operations to calculate personal bonus.");
            } else {
                $this->_log->warn("Cannot get period to calculate personal bonus.");
            }
        }
    }

    private function _calcBonusPv()
    {
        /** @var  $helper Praxigento_Bonus_Helper_Data */
        $helper = Config::helper();
        $periodCode = $helper->cfgPersonalBonusPeriod();
        $typePeriod = $this->_getTypePeriod($periodCode);
        $typeCalc = $this->_getTypeCalc(Config::CALC_BONUS_PERSONAL);
        $typeOperPvInt = $this->_getTypeOperation(Config::OPER_PV_INT);
        $typeOperPvOrder = $this->_getTypeOperation(Config::OPER_ORDER_PV);
        $operIds = array($typeOperPvInt->getId(), $typeOperPvOrder->getId());
        /* get calculation period */
        $result = null;
        /** @var  $req  Praxigento_Bonus_Service_Period_Request_GetPeriodForPersonalBonus */
        $req = Mage::getModel(Config::CFG_SERVICE . '/period_request_getPeriodForPersonalBonus');
        $req->setBonusTypeId($typeCalc->getId());
        $req->setOperationTypeIds($operIds);
        $req->setPeriodCode($periodCode);
        $req->setPeriodTypeId($typeCalc->getId());
        /** @var  $call Praxigento_Bonus_Service_Period_Call */
        $call = Mage::getModel(Config::CFG_SERVICE . '/period_call');
        /** @var  $resp Praxigento_Bonus_Service_Period_Response_GetPeriodForPersonalBonus */
        $resp = $call->getPeriodForPersonalBonus($req);
        if ($resp->isSucceed()) {
            /** @var  $balance Praxigento_Bonus_Model_Own_Period */
            $period = Mage::getModel('prxgt_bonus_model/period');
            if ($resp->isNewPeriod()) {
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
            if ($resp->getErrorCode() == GetPeriodForPersonalBonus::ERR_NOTHING_TO_DO) {
                $this->_log->warn("There are no periods/operations to calculate personal bonus.");
            } else {
                $this->_log->warn("Cannot get period to calculate personal bonus.");
            }
        }
    }

    private function _getOperationsForPersonalBonus($operIds = array(), $period, $periodCode)
    {
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
        foreach ($operIds as $one) {
            $fields[] = Operation::ATTR_TYPE_ID;
            $values[] = $one;
        }
        $collection->addFieldToFilter($fields, $values);
        $tableTrnx = $collection->getTable(Config::CFG_MODEL . '/' . Config::ENTITY_TRANSACTION);
        $collection->getSelect()->joinLeft(
            array('trnx' => $tableTrnx),
            'main_table.id = trnx.operation_id',
            '*'
        );
        $fldDate = 'trnx.' . Transaction::ATTR_DATE_APPLIED;
        $from = Config::helperPeriod()->calcPeriodFromTs($period, $periodCode);
        $to = Config::helperPeriod()->calcPeriodToTs($period, $periodCode);
        $collection->addFieldToFilter($fldDate, array('gteq' => $from));
        $collection->addFieldToFilter($fldDate, array('lteq' => $to));
        $sql = $collection->getSelectSql(true);
        return $collection;
    }

    private function _createCatalogCategories()
    {
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

    private function _createProducts()
    {
        $be = Mage::getModel('nmmlm_core_model/dict_catalog_product');
//        $be->setBeId(100);
        $be->setEnabled(true);
        $be->setMageSku('sku1213');
        $be->setName(new Nmmlm_Core_Model_Dict_I18n_Text("product1"));
        $be->setDescription(new Nmmlm_Core_Model_Dict_I18n_Text('description'));

        $be->setMageCategoryIds(array($this->_categoryElectronics->getId()));

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

    private function _createCustomers()
    {
        $records = $this->_readDataCustomers($this->_fileNameCustomers);
        $count = sizeof($records);
        if ($count) {
            $this->_log->debug("Total $count lines are read from file {$this->_fileNameCustomers}");
            /* create customers and generate MLM IDs */
            foreach ($records as $one) {
                $this->_createCustomerEntry($one);
            }
            /* set up Upline and MLM Path */
            foreach ($records as $one) {
                $this->_updateUpline($one);
            }
        }
    }

    /**
     * Read file with data, parse and return array of Records.
     * @param $path
     * @return RecordCustomer[]
     */
    private function _readDataCustomers($path)
    {
        $result = array();
        /* registry to uniquelize emails */
        $emailReg = array();
        if (file_exists($path)) {
            $content = file($path);
            foreach ($content as $one) {
                $data = explode(',', trim($one));
                $obj = new RecordCustomer();
                $obj->mlmId = $data[0];
                $obj->mlmUpline = $data[1];
                $obj->nameFirst = $data[2];
                $obj->nameLast = $data[3];
                $obj->groupId = $data[5];
                /**/
                $email = strtolower(trim($data[4]));
                if (isset($emailReg[$email])) {
                    $emailReg[$email]++;
                    $parts = explode('@', $email);
                    $email = $parts[0] . $emailReg[$email] . '@' . $parts[1];
                } else {
                    $emailReg[$email] = 0;
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

    private function _createCustomerEntry(RecordCustomer $rec)
    {
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
            $this->_regUpline[$rec->mlmId] = $customer->getData(Nmmlm_Core_Config::ATTR_CUST_MLM_ID);
        } catch (Exception $e) {
            $this->_log->error("Cannot save customer '$nameFirst $nameLast <$email>'.", $e);
        }
    }

    private function _updateUpline(RecordCustomer $rec)
    {
        $mlmId = $this->_regUpline[$rec->mlmId];
        $mlmUpline = isset($this->_regUpline[$rec->mlmUpline]) ?
            $this->_regUpline[$rec->mlmUpline] : self::DEFAULT_UPLINE;
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
    private function _createOrders()
    {
        $records = $this->_readDataOrders($this->_fileNameOrders);
        /** @var  $allProducts Innoexts_WarehousePlus_Model_Mysql4_Catalog_Product_Collection */
        $allProducts = Mage::getModel('catalog/product')->getCollection();
        /** @var  $product Mage_Catalog_Model_Product */
        $product = $allProducts->getFirstItem();
        foreach ($records as $one) {
            $this->_createOrderForCustomer($one, $product);
        }

    }

    /**
     * Read file with data, parse and return array of Records.
     * @param $path
     * @return RecordCustomer[]
     */
    private function _readDataOrders($path)
    {
        $result = array();
        if (file_exists($path)) {
            $content = file($path);
            foreach ($content as $one) {
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
     * @param $path
     * @return RecordPvTransfer[]
     */
    private function _readDataPvTransfers($path)
    {
        $result = array();
        if (file_exists($path)) {
            $content = file($path);
            foreach ($content as $one) {
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
        Mage_Catalog_Model_Product $product)
    {
        $customer = Nmmlm_Core_Util::findCustomerByMlmId($record->mlmId);
        /** @var  $quote Mage_Sales_Model_Quote */
        $quote = Mage::getModel('sales/quote')->setStoreId(Mage::app()->getStore('default')->getId());
        $quote->assignCustomer($customer);
        $buyInfo = array('qty' => 1);
        $quote->addProduct($product, new Varien_Object($buyInfo));

        $addressData = array(
            'firstname' => 'Test',
            'lastname' => 'Test',
            'street' => 'Sample Street 10',
            'city' => 'Somewhere',
            'postcode' => '123456',
            'telephone' => '123456',
            'country_id' => 'US',
            'region_id' => 12, // id from directory_country_region table
        );
        $quote->getBillingAddress()->addData($addressData);
        $shippingAddress = $quote->getShippingAddress()->addData($addressData);
        $shippingAddress->setCollectShippingRates(true)->collectShippingRates()
            ->setShippingMethod('flatrate_flatrate')
            ->setPaymentMethod('checkmo');

        $quote->getPayment()->importData(array('method' => 'checkmo'));

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

    /**
     * Add random data to orders log.
     */
    private function _populateLogOrder()
    {
        $bonus = $this->_getTypeCalc(Config::CALC_BONUS_PERSONAL);
        $bonusId = $bonus->getId();
        /** @var  $helper Nmmlm_Core_Helper_Data */
        $helper = Mage::helper('nmmlm_core_helper');
        $allOrders = Mage::getModel('sales/order')->getCollection();
        /** @var  $one Mage_Sales_Model_Order */
        foreach ($allOrders as $one) {
            $orderId = $one->getId();
            /** @var  $log Praxigento_Bonus_Model_Own_Log_Order */
            $log = Mage::getModel('prxgt_bonus_model/log_order');
            $createdAt = $one->getCreatedAt();
            $date = $helper->convertToDateTime($createdAt);
            $formatted = date_format($date, Nmmlm_Core_Config::FORMAT_DATETIME);
            $log->setDateChanged($formatted);
            $log->setOrderId($orderId);
            $log->setTypeId($bonusId);
            $log->setValue($one->getData(Nmmlm_Core_Config::ATTR_COMMON_PV_TOTAL));
            $log->getResource()->save($log);
        }
    }

    /**
     * @param $code
     * @return Praxigento_Bonus_Model_Own_Type_Asset
     */
    private function _getTypeAsset($code)
    {
        if (is_null($this->_cacheAssetTypes)) {
            $allTypes = Mage::getModel('prxgt_bonus_model/type_asset')->getCollection();
            $types = array();
            /** @var  $one Praxigento_Bonus_Model_Own_Type_Asset */
            foreach ($allTypes as $one) {
                $types[$one->getCode()] = $one;
            }
            $this->_cacheAssetTypes = $types;
        }
        $result = $this->_cacheAssetTypes[$code];
        return $result;
    }

    /**
     * @param $code
     * @return Praxigento_Bonus_Model_Own_Type_Calc
     */
    private function _getTypeCalc($code)
    {
        if (is_null($this->_cacheBonusTypes)) {
            $allTypes = Mage::getModel('prxgt_bonus_model/type_calc')->getCollection();
            $types = array();
            /** @var  $one Praxigento_Bonus_Model_Own_Type_Calc */
            foreach ($allTypes as $one) {
                $types[$one->getCode()] = $one;
            }
            $this->_cacheBonusTypes = $types;
        }
        $result = $this->_cacheBonusTypes[$code];
        return $result;
    }

    /**
     * @param $code
     * @return Praxigento_Bonus_Model_Own_Type_Oper
     */
    private function _getTypeOperation($code)
    {
        if (is_null($this->_cacheOperationTypes)) {
            $allTypes = Mage::getModel('prxgt_bonus_model/type_oper')->getCollection();
            $types = array();
            /** @var  $one Praxigento_Bonus_Model_Own_Type_Operation */
            foreach ($allTypes as $one) {
                $types[$one->getCode()] = $one;
            }
            $this->_cacheOperationTypes = $types;
        }
        $result = $this->_cacheOperationTypes[$code];
        return $result;
    }

    /**
     * @param $code
     * @return Praxigento_Bonus_Model_Own_Type_Period
     */
    private function _getTypePeriod($code)
    {
        if (is_null($this->_cachePeriodTypes)) {
            $allTypes = Mage::getModel('prxgt_bonus_model/type_period')->getCollection();
            $types = array();
            /** @var  $one Praxigento_Bonus_Model_Own_Type_Period */
            foreach ($allTypes as $one) {
                $types[$one->getCode()] = $one;
            }
            $this->_cachePeriodTypes = $types;
        }
        $result = $this->_cachePeriodTypes[$code];
        return $result;
    }

    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f sample_data.php [options]

  --create      Create sample data.
  --emulate     Emulate operations.
  --bonusPv     Calculate PV bonus.

USAGE;
    }

    private function _emulatePvTransferOperations()
    {
        /** @var  $opType Praxigento_Bonus_Model_Own_Type_Operation */
        $opType = $this->_getTypeOperation(Praxigento_Bonus_Config::OPER_PV_INT);
        /** @var  $assetType Praxigento_Bonus_Model_Own_Type_Asset */
        $assetType = $this->_getTypeAsset(Praxigento_Bonus_Config::ASSET_PV);

        $records = $this->_readDataPvTransfers($this->_fileNamePvTransfers);
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        foreach ($records as $one) {
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
                if ($pv != 0) {
                    /* create transaction */
                    $this->_createTransaction($operationId, $accountFrom->getId(), $accountTo->getId(), $pv, $date);
                }
                $connection->commit();
            } catch (Exception $e) {
                $connection->rollback();
            }
        }
    }

    private function _getStoreCustomerId()
    {
        $result = 1;
        return $result;
    }

    /**
     * Save operations and transactions and update customer balances according to sales orders.
     */
    private function _emulateOrderOperations()
    {
        $opType = $this->_getTypeOperation(Praxigento_Bonus_Config::OPER_ORDER_PV);
        $assetType = $this->_getTypeAsset(Praxigento_Bonus_Config::ASSET_PV);
        /** @var  $helper Nmmlm_Core_Helper_Data */
        $helper = Mage::helper('nmmlm_core_helper');
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $allOrders = Mage::getModel('sales/order')->getCollection();
        foreach ($allOrders as $one) {
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
                if ($pv != 0) {
                    /* create transaction */
                    $this->_createTransaction($operationId, $accountStoreId, $accountCustomerId, $pv, $date);
                }
                $connection->commit();
            } catch (Exception $e) {
                $connection->rollback();
            }
        }
    }

    /**
     * Select customer account by asset and customer ids or create new one.
     *
     * @param $assetId
     * @param $customerId
     * @return Praxigento_Bonus_Model_Own_Account|Varien_Object
     */
    private function _getAssetAccountByCustomerId($assetId, $customerId)
    {
        /*  */
        /** @var  $account Praxigento_Bonus_Resource_Own_Account_Collection */
        $accountCollection = Mage::getModel('prxgt_bonus_model/account')->getCollection();
        $accountCollection->addFieldToFilter(Praxigento_Bonus_Model_Own_Account::ATTR_CUSTOMER_ID, $customerId);
        $accountCollection->addFieldToFilter(Praxigento_Bonus_Model_Own_Account::ATTR_ASSET_ID, $assetId);
        /** @var  $account Praxigento_Bonus_Model_Own_Account */
        $result = Mage::getModel('prxgt_bonus_model/account');
        if ($accountCollection->getSize()) {
            $result = $accountCollection->getFirstItem();
        } else {
            /* create new account */
            $result->setCustomerId($customerId);
            $result->setAssetId($assetId);
            $result->save();
        }
        return $result;
    }

    private function _updateBalance($accountId, $value, $period = Praxigento_Bonus_Config::PERIOD_KEY_NOW)
    {
        /** @var  $balanceCollection Praxigento_Bonus_Resource_Own_Balance_Collection */
        $balanceCollection = Mage::getModel('prxgt_bonus_model/balance')->getCollection();
        $balanceCollection->addFieldToFilter(Balance::ATTR_ACCOUNT_ID, $accountId);
        $balanceCollection->addFieldToFilter(Balance::ATTR_PERIOD, $period);
        /** @var  $balance Praxigento_Bonus_Model_Own_Balance */
        $balance = Mage::getModel('prxgt_bonus_model/balance');
        if ($balanceCollection->getSize()) {
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

    private function _createTransaction($operationId, $debitId, $creditId, $value, $date)
    {
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
     * @return int see PHP function usort()
     */
    public static function compareByUpline(RecordCustomer $a, RecordCustomer $b)
    {
        $aa = (int)$a->mlmUpline;
        $bb = (int)$b->mlmUpline;
        if ($aa == $bb) {
            $result = 0;
        } else {
            return ($aa < $bb) ? -1 : 1;
        }
        return $result;
    }
}

/**
 * Class RecordCustomer Bean to group data from data file with customer info.
 */
class RecordCustomer
{
    public $mlmId;
    public $mlmUpline;
    public $nameFirst;
    public $nameLast;
    public $email;
    public $groupId;
}

/**
 * Class RecordOrder Bean to group data from data file with orders info.
 */
class RecordOrder
{
    public $mlmId;
    public $orderDate;
    public $orderAmount;
    public $orderPv;
}

/**
 * Class RecordPvTransfer Bean to group data from data file with internal PV transfers info.
 */
class RecordPvTransfer
{
    public $date;
    public $mlmIdFrom;
    public $mlmIdTo;
    public $pv;
}

/* prevent Notice: A session had already been started */
session_start();
$shell = new Praxigento_Shell();
$shell->run();
