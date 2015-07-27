<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */

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
    /** @var Logger */
    private $_log;
    private $_fileNameCustomers;
    private $_fileNameOrders;
    /**
     * Registry to store customer data with Sponsor (Upline) ID as a key.
     *
     *
     * @var array
     */
    private $_regUpline = array();
    private $_categoryElectronics;
    /** @var  array of bonus types; 'code' is the key. */
    private $_cacheBonusTypes;

    public function __construct()
    {
        parent::__construct();
        $this->_log = Praxigento_Log_Logger::getLogger(__CLASS__);
        $this->_fileNameCustomers = dirname($_SERVER['SCRIPT_NAME']) . '/data_customers.csv';
        $this->_fileNameOrders = dirname($_SERVER['SCRIPT_NAME']) . '/data_orders.csv';
    }

    /**
     * Run script
     *
     */
    public function run()
    {
        $create = $this->getArg(self::OPT_CREATE);
        if ($create) {
            $this->_log->debug("Sample data generation is started.");
//            $this->_createCatalogCategories();
//            $this->_createProducts();
//            $this->_createCustomers();
            $this->_createOrders();
            /* add fake data to bonus module */
            $this->_populateLogOrder();
            $this->_log->debug("Sample data generation is completed.");
            echo "Done.\n";
        } else {
            echo $this->usageHelp();
        }

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
        $order->setData(Nmmlm_Core_Config::ATTR_COMMON_PV_TOTAL, $record->orderPv);

        $order->getResource()->save($order);
        $this->_log->debug("Order #" . $order->getIncrementId() . " is created.");

    }

    /**
     * Add random data to orders log.
     */
    private function _populateLogOrder()
    {
        $bonus = $this->_getBonusTypeByCode(Praxigento_Bonus_Config::BONUS_PERSONAL);
        $bonusId = $bonus->getId();
        $allOrders = Mage::getModel('sales/order')->getCollection();
        /** @var  $one Mage_Sales_Model_Order */
        foreach ($allOrders as $one) {
            $orderId = $one->getId();
            /** @var  $log Praxigento_Bonus_Model_Own_Log_Order */
            $log = Mage::getModel('prxgt_bonus_model/log_order');
            $log->setOrderId($orderId);
            $log->setTypeId($bonusId);
            $log->setValue($one->getData(Nmmlm_Core_Config::ATTR_COMMON_PV_TOTAL));
            $log->getResource()->save($log);
        }

    }

    private function _getBonusTypeByCode($code)
    {
        if (is_null($this->_cacheBonusTypes)) {
            $allBonusTypes = Mage::getModel('prxgt_bonus_model/core_type')->getCollection();
            $types = array();
            /** @var  $one Praxigento_Bonus_Model_Own_Core_Type */
            foreach ($allBonusTypes as $one) {
                $types[$one->getCode()] = $one;
            }
            $this->_cacheBonusTypes = $types;
        }
        $result = $this->_cacheBonusTypes[$code];
        return $result;
    }

    private function _randomAmount()
    {
        $result = rand(1, 10000) / 100;
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

USAGE;
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

/* prevent Notice: A session had already been started */
session_start();
$shell = new Praxigento_Shell();
$shell->run();
