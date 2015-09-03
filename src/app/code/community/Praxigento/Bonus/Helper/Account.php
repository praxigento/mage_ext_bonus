<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;
use Praxigento_Bonus_Model_Own_Account as Account;
use Praxigento_Bonus_Model_Own_Type_Asset as TypeAsset;

/**
 * Account related utilities.
 *
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Helper_Account {
    /**
     * Data for Magento Accountant (The special customer whose accounts are used in transactions as Magento Store
     * accounts).
     *
     * @var array [assetCode] => accountData
     */
    private static $_cachedAccountantData = array();
    /**
     * Entity ID for Magento Accountant customer.
     *
     * @var int
     */
    private static $_cachedAccountantMageId;

    /**
     * Return account data for Magento Accountant by asset code (Praxigento_Bonus_Config::ASSET_).
     *
     * @param $assetCode
     *
     * @return Praxigento_Bonus_Model_Own_Account
     */
    public function getAccountantAccByAssetCode($assetCode) {
        /* get all existing accounts for Accountant */
        if(count(self::$_cachedAccountantData) == 0) {
            $this->_loadAccounts();
        }
        if(!isset(self::$_cachedAccountantData[ $assetCode ])) {
            $acc                                       = $this->_createAccount($assetCode);
            self::$_cachedAccountantData[ $assetCode ] = $acc;
        }
        $result = self::$_cachedAccountantData[ $assetCode ];
        return $result;
    }

    /**
     * Return account id for Magento Accountant by asset code (Praxigento_Bonus_Config::ASSET_).
     *
     * @param $assetCode
     *
     * @return int
     */
    public function getAccountantAccIdByAssetCode($assetCode) {
        /* get all existing accounts for Accountant */
        if(count(self::$_cachedAccountantData) == 0) {
            $this->_loadAccounts();
        }
        if(!isset(self::$_cachedAccountantData[ $assetCode ])) {
            $acc                                       = $this->_createAccount($assetCode);
            self::$_cachedAccountantData[ $assetCode ] = $acc;
        }
        /** @var  $model Praxigento_Bonus_Model_Own_Account */
        $model  = self::$_cachedAccountantData[ $assetCode ];
        $result = $model->getId();
        return $result;
    }

    /**
     * Load accounts from db and cache it.
     */
    private function _loadAccounts() {
        $all    = Config::get()->collectionAccount();
        $custId = $this->getAccountantMageId();
        $all->addFieldToFilter(Account::ATTR_CUSTOMER_ID, $custId);
        /* join assets to get codes */
        $asAsset = 'a';
        $table   = array( $asAsset => Config::CFG_MODEL . '/' . Config::ENTITY_TYPE_ASSET );
        $cond    = 'main_table.' . Account::ATTR_ASSET_ID . '='
                   . $asAsset . '.' . TypeAsset::ATTR_ID;
        $cols    = array( TypeAsset::ATTR_CODE );
        $all->join($table, $cond, $cols);
        foreach($all as $one) {
            $accId   = $one->getData(Account::ATTR_ID);
            $assetId = $one->getData(Account::ATTR_ASSET_ID);
            $custId  = $one->getData(Account::ATTR_CUSTOMER_ID);
            $code    = $one->getData(TypeAsset::ATTR_CODE);
            $item    = new Varien_Object();
            $item->setId($accId);
            $item->setAssetId($assetId);
            $item->setCustomerId($custId);
            self::$_cachedAccountantData[ $code ] = $item;
        }
    }

    private function _createAccount($assetCode) {
        $assetId = Config::get()->helperType()->getAssetId($assetCode);
        $custId  = $this->getAccountantMageId();
        $result  = Config::get()->modelAccount();
        $result->setAssetId($assetId);
        $result->setCustomerId($custId);
        $result->save();
        return $result;
    }

    public function getAccountantMageId() {
        if(is_null(self::$_cachedAccountantMageId)) {
            $mlmId   = Config::get()->helper()->cfgGeneralAccountantMlmId();
            $hlpCore = Config::get()->helperCore();
            /** @var  $cust Nmmlm_Core_Model_Customer_Customer */
            $cust                          = $hlpCore->findCustomerByMlmId($mlmId);
            self::$_cachedAccountantMageId = $cust->getId();
        }
        return self::$_cachedAccountantMageId;
    }
}