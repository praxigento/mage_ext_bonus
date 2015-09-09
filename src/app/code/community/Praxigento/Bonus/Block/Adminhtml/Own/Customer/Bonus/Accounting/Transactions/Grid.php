<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Mage_Eav_Model_Entity as Eav;
use Nmmlm_Core_Config as ConfigCore;
use Praxigento_Bonus_Config as Config;
use Praxigento_Bonus_Model_Own_Account as Account;
use Praxigento_Bonus_Model_Own_Operation as Operation;
use Praxigento_Bonus_Model_Own_Transaction as Transaction;
use Praxigento_Bonus_Model_Own_Type_Asset as TypeAsset;
use Praxigento_Bonus_Model_Own_Type_Oper as TypeOper;

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Block_Adminhtml_Own_Customer_Bonus_Accounting_Transactions_Grid
    extends Mage_Adminhtml_Block_Widget_Grid {

    const AS_ASSET_CODE = 'asset_code';
    const AS_CREDIT_CUST = 'credit_cust';
    const AS_DATE_APPLIED = 'date_applied';
    const AS_DATE_PERFORMED = 'date_performed';
    const AS_DEBIT_CUST = 'debit_cust';
    const AS_OPER_CODE = 'oper_code';
    const AS_OPER_ID = 'oper_id';
    const AS_TRAN_ID = 'tran_id';
    const AS_VALUE = 'value';

    public function __construct() {
        parent::__construct();
        $this->setId('prxgt_bonus_grid_transactions');
        $this->setDefaultSort(self::AS_TRAN_ID);
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {
        /**
         * SELECT
         * trnx.id,
         * oper.id,
         * oper.date_performed,
         * trnx.date_applied,
         * operType.code,
         * custDeb.nmmlm_core_mlm_id,
         * custCred.nmmlm_core_mlm_id,
         * trnx.value,
         * asset.code
         * FROM prxgt_bonus_trnx trnx
         * LEFT JOIN prxgt_bonus_operation oper
         * ON trnx.operation_id = oper.id
         * LEFT JOIN prxgt_bonus_account credit
         * ON trnx.credit_acc_id = credit.id
         * LEFT JOIN prxgt_bonus_account debit
         * ON trnx.debit_acc_id = debit.id
         * LEFT JOIN customer_entity custCred
         * ON credit.customer_id = custCred.entity_id
         * LEFT JOIN customer_entity custDeb
         * ON debit.customer_id = custDeb.entity_id
         * LEFT JOIN prxgt_bonus_type_asset asset
         * ON credit.asset_id = asset.id
         * LEFT JOIN prxgt_bonus_type_oper operType ON oper.type_id = operType.id
         */
        $collection = Config::get()->collectionTransaction();
        $asTrnx = 'main_table';
        $collection->addFieldToSelect(Transaction::ATTR_ID, self::AS_TRAN_ID);
        $collection->addFieldToSelect(Transaction::ATTR_DATE_APPLIED, self::AS_DATE_APPLIED);
        $collection->addFieldToSelect(Transaction::ATTR_VALUE, self::AS_VALUE);
        /* LEFT JOIN prxgt_bonus_operation oper ON trnx.operation_id = oper.id */
        $asOper = 'oper';
        $tbl = array( $asOper => Config::CFG_MODEL . '/' . Config::ENTITY_OPERATION );
        $cond = $asTrnx . '.' . Transaction::ATTR_OPERATION_ID . '=' . $asOper . '.' . Operation::ATTR_ID;
        $cols = array(
            self::AS_OPER_ID        => Operation::ATTR_ID,
            self::AS_DATE_PERFORMED => Operation::ATTR_DATE_PERFORMED
        );
        $collection->join($tbl, $cond, $cols);
        /* LEFT JOIN prxgt_bonus_account credit ON trnx.credit_acc_id = credit.id */
        $asCredit = 'credit';
        $tbl = array( $asCredit => Config::CFG_MODEL . '/' . Config::ENTITY_ACCOUNT );
        $cond = $asTrnx . '.' . Transaction::ATTR_CREDIT_ACC_ID . '=' . $asCredit . '.' . Account::ATTR_ID;
        $cols = array();
        $collection->join($tbl, $cond, $cols);
        /* LEFT JOIN prxgt_bonus_account debit ON trnx.debit_acc_id = debit.id */
        $asDebit = 'debit';
        $tbl = array( $asDebit => Config::CFG_MODEL . '/' . Config::ENTITY_ACCOUNT );
        $cond = $asTrnx . '.' . Transaction::ATTR_DEBIT_ACC_ID . '=' . $asDebit . '.' . Account::ATTR_ID;
        $cols = array();
        $collection->join($tbl, $cond, $cols);
        /* LEFT JOIN customer_entity custCred ON credit.customer_id = custCred.entity_id */
        $asCreditCust = 'creditCust';
        $tbl = array( $asCreditCust => 'customer/entity' );
        $cond = $asCredit . '.' . Account::ATTR_CUSTOMER_ID . '=' . $asCreditCust . '.' . Eav::DEFAULT_ENTITY_ID_FIELD;
        $cols = array( self::AS_CREDIT_CUST => ConfigCore::ATTR_CUST_MLM_ID );
        $collection->join($tbl, $cond, $cols);
        /* LEFT JOIN customer_entity custDeb ON debit.customer_id = custDeb.entity_id */
        $asDebitCust = 'debitCust';
        $tbl = array( $asDebitCust => 'customer/entity' );
        $cond = $asDebit . '.' . Account::ATTR_CUSTOMER_ID . '=' . $asDebitCust . '.' . Eav::DEFAULT_ENTITY_ID_FIELD;
        $cols = array( self::AS_DEBIT_CUST => ConfigCore::ATTR_CUST_MLM_ID );
        $collection->join($tbl, $cond, $cols);
        /* LEFT JOIN prxgt_bonus_type_asset asset ON credit.asset_id = asset.id */
        $asAsset = 'asset';
        $tbl = array( $asAsset => Config::CFG_MODEL . '/' . Config::ENTITY_TYPE_ASSET );
        $cond = $asCredit . '.' . Account::ATTR_ASSET_ID . '=' . $asAsset . '.' . TypeAsset::ATTR_ID;
        $cols = array( self::AS_ASSET_CODE => TypeAsset::ATTR_CODE );
        $collection->join($tbl, $cond, $cols);
        /* LEFT JOIN prxgt_bonus_type_oper operType ON oper.type_id = operType.id */
        $asOperType = 'operType';
        $tbl = array( $asOperType => Config::CFG_MODEL . '/' . Config::ENTITY_TYPE_OPER );
        $cond = $asOper . '.' . Operation::ATTR_TYPE_ID . '=' . $asOperType . '.' . TypeOper::ATTR_ID;
        $cols = array( self::AS_OPER_CODE => TypeOper::ATTR_CODE );
        $collection->join($tbl, $cond, $cols);
        /* prepare collection */
        $sql = $collection->getSelectSql(true);
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns() {
        $helper = Config::get()->helper();


        $this->addColumn(self::AS_TRAN_ID, array(
            'header' => $helper->__('Trn #'),
            'filter' => false,
            'index'  => self::AS_TRAN_ID
        ));

        $this->addColumn(self::AS_OPER_ID, array(
            'header' => $helper->__('Opr #'),
            'filter' => false,
            'index'  => self::AS_OPER_ID
        ));

        $this->addColumn(self::AS_DATE_PERFORMED, array(
            'header' => $helper->__('Performed at'),
            'filter' => false,
            'index'  => self::AS_DATE_PERFORMED,
            'type'   => 'datetime'
        ));

        $this->addColumn(self::AS_DATE_APPLIED, array(
            'header' => $helper->__('Applied to'),
            'filter' => false,
            'index'  => self::AS_DATE_APPLIED,
            'type'   => 'datetime'
        ));

        $this->addColumn(self::AS_OPER_CODE, array(
            'header' => $helper->__('Opr code'),
            'filter' => false,
            'index'  => self::AS_OPER_CODE
        ));

        $this->addColumn(self::AS_DEBIT_CUST, array(
            'header' => $helper->__('Debit'),
            'filter' => false,
            'index'  => self::AS_DEBIT_CUST
        ));

        $this->addColumn(self::AS_CREDIT_CUST, array(
            'header' => $helper->__('Credit'),
            'filter' => false,
            'index'  => self::AS_CREDIT_CUST
        ));

        $this->addColumn(self::AS_VALUE, array(
            'header' => $helper->__('Value'),
            'index'  => self::AS_VALUE,
            'type'   => 'currency',
            'filter' => false
        ));

        $this->addColumn(self::AS_ASSET_CODE, array(
            'header' => $helper->__('Asset'),
            'filter' => false,
            'index'  => self::AS_ASSET_CODE
        ));

        return parent::_prepareColumns();
    }

}