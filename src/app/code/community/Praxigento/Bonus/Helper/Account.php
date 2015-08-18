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
class Praxigento_Bonus_Helper_Account
{
    private static $_cachedAccountantData = array();
    private static $_cachedAccountantMageId;


    public function getAccountantAccByAssetCode($assetCode)
    {
        /* get all existing accounts for Accountant */
        if (count(self::$_cachedAccountantData) == 0) {
            $this->_loadAccounts();
        }
        if (!isset(self::$_cachedAccountantData[$assetCode])) {
            $acc = $this->_createAccount($assetCode);
            self::$_cachedAccountantData[$assetCode] = $acc;
        }
        $result = self::$_cachedAccountantData[$assetCode];
        return $result;
    }

    private function _loadAccounts()
    {
        $all = Config::get()->collectionAccount();
        $custId = $this->getAccountantMageId();
        $all->addFieldToFilter(Account::ATTR_CUSTOMER_ID, $custId);
        /* join assets to get codes */
        $asAsset = 'a';
        $table = array($asAsset => Config::CFG_MODEL . '/' . Config::ENTITY_TYPE_ASSET);
        $cond = 'main_table.' . Account::ATTR_ASSET_ID . '='
            . $asAsset . '.' . TypeAsset::ATTR_ID;
        $cols = array(TypeAsset::ATTR_CODE);
        $all->join($table, $cond, $cols);
        foreach ($all as $one) {
            $account = Config::get()->modelAccount();
            $account->setId($one->getData(Account::ATTR_ID));
            $account->setAssetId($one->getData(Account::ATTR_ASSET_ID));
            $account->setCustomerId($one->getData(Account::ATTR_CUSTOMER_ID));
            $code = $one->getData(TypeAsset::ATTR_CODE);
            self::$_cachedAccountantData[$code] = $account;
        }
    }

    private function _createAccount($assetCode)
    {
        $assetId = Config::get()->helperType()->getAssetId($assetCode);
        $custId = $this->getAccountantMageId();
        $result = Config::get()->modelAccount();
        $result->setAssetId($assetId);
        $result->setCustomerId($custId);
        $result->save();
        return $result;
    }

    public function getAccountantMageId()
    {
        if (is_null(self::$_cachedAccountantMageId)) {
            $mlmId = Config::get()->helper()->cfgGeneralAccountantMlmId();
            $hlpCore = Config::get()->helperCore();
            /** @var  $cust Nmmlm_Core_Model_Customer_Customer */
            $cust = $hlpCore->findCustomerByMlmId($mlmId);
            self::$_cachedAccountantMageId = $cust->getId();
        }
        return self::$_cachedAccountantMageId;
    }
}