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

class Praxigento_Dcp_Shell extends Mage_Shell_Abstract
{
    const DEFAULT_UPLINE = 100000001;
    const OPT_SOURCE = 'source';
    private $_log;
    /**
     * Registry to store customer data with Sponsor (Upline) ID as a key.
     *
     *
     * @var array
     */
    private $_regUpline = array();

    public function __construct()
    {
        parent::__construct();
        $this->_log = Praxigento_Log_Logger::getLogger(__CLASS__);
    }

    /**
     * Run script
     *
     */
    public function run()
    {
        $source = $this->getArg(self::OPT_SOURCE);
        if ($source) {
            $records = $this->_readFile($source);
            $count = sizeof($records);
            if ($count) {
                $this->_log->info("Total $count records are read.");
                /* create customers and generate MLM IDs */
                foreach ($records as $one) {
                    $this->_createCustomers($one);
                }
                /* set up Upline and MLM Path */
                foreach ($records as $one) {
                    $this->_updateUpline($one);
                }
            }
            echo "done from $source!\n";
        } else {
            echo $this->usageHelp();
        }

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

    private function _createCustomers(Record $rec)
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
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f test_data_add.php [options]

  --source <path>               Add customers to Magneto from file with customer data.

USAGE;
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
$shell = new Praxigento_Dcp_Shell();
$shell->run();
