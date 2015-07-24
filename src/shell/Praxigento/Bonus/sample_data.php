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
    /**
     * Registry to store customer data with Sponsor (Upline) ID as a key.
     *
     *
     * @var array
     */
    private $_regUpline = array();
    private $_categoryElectronics;

    public function __construct()
    {
        parent::__construct();
        $this->_log = Praxigento_Log_Logger::getLogger(__CLASS__);
        $this->_fileNameCustomers = dirname($_SERVER['SCRIPT_NAME']) . '/data_customers.csv';
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
     * Run script
     *
     */
    public function run()
    {
        $create = $this->getArg(self::OPT_CREATE);
        if ($create) {
            $this->_log->debug("Sample data generation is started.");
            $this->_createCatalogCategories();
            $this->_createProducts();
            $this->_createCustomers();
            $this->_log->debug("Sample data generation is completed.");
            echo "Done.\n";
        } else {
            echo $this->usageHelp();
        }

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

    /**
     * Read file with data, parse and return array of Records.
     * @param $path
     * @return Record[]
     */
    private function _readFile($path)
    {
        $result = array();
        /* registry to uniquelize emails */
        $emailReg = array();
        if (file_exists($path)) {
            $content = file($path);
            foreach ($content as $one) {
                $data = explode(',', trim($one));
                $obj = new Record();
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

    private function _createCustomers()
    {
        $records = $this->_readFile($this->_fileNameCustomers);
        $count = sizeof($records);
        if ($count) {
            $this->_log->debug("Total $count lines are read from file {$this->_fileNameCustomers}");
            /* create customers and generate MLM IDs */
            foreach ($records as $one) {
                $this->_createCustomerEnrty($one);
            }
            /* set up Upline and MLM Path */
            foreach ($records as $one) {
                $this->_updateUpline($one);
            }
        }
    }

    private function _createCustomerEnrty(Record $rec)
    {
        $nameFirst = $rec->nameFirst;
        $nameLast = $rec->nameLast;
//        $email = str_replace('@', '_', $rec->email) . '@praxigento.com';
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

    private function _updateUpline(Record $rec)
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
     * Compare two Record objects by Upline ID.
     *
     * <code>
     *      $result = array(array('value'=>'...', 'label'=>'...'), ...);
     *      usort($result, array('Praxigento_Ad_Shell', 'compareByUpline'));
     * </code>
     *
     * @param Record $a
     * @param Record $b
     * @return int see PHP function usort()
     */
    public static function compareByUpline(Record $a, Record $b)
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
 * Class Record Bean to group data from data file with customer info.
 */
class Record
{
    public $mlmId;
    public $mlmUpline;
    public $nameFirst;
    public $nameLast;
    public $email;
    public $groupId;
}

/* prevent Notice: A session had already been started */
session_start();
$shell = new Praxigento_Shell();
$shell->run();
