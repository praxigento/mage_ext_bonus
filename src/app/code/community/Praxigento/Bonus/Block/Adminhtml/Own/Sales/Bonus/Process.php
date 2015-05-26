<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Block_Adminhtml_Own_Sales_Bonus_Process extends Praxigento_Bonus_Block_Adminhtml_Own_Base
{
    private $_ordersCount = null;

    public function getOrdersCount()
    {
        if (is_null($this->_ordersCount)) {
            $this->_initOrdersCount();
        }
        return $this->_ordersCount;

    }

    private function _initOrdersCount()
    {
        /** @var  $rsrc Mage_Core_Model_Resource */
        $rsrc = Mage::getSingleton('core/resource');
        /** @var  \Varien_Db_Adapter_Pdo_Mysql */
        $conn = $rsrc->getConnection('core_write');
        $tblSales = $rsrc->getTableName('sales/order');
        $tblRetail = $rsrc->getTableName(Config::CFG_MODEL . '/' . Config::CFG_ENTITY_ORDER);
        $as = 'NUM';
        $query = "SELECT
  COUNT($tblSales.entity_id) AS $as
FROM $tblSales
  LEFT OUTER JOIN $tblRetail
    ON $tblSales.entity_id = $tblRetail.order_id
WHERE $tblRetail.order_id IS NULL";
        $rs = $conn->query($query);
        $arr = $rs->fetch();
        $this->_ordersCount = $arr[$as];
    }

    public function isRetailBonusEnabled()
    {
        /** @var  $hlp Praxigento_Bonus_Helper_Data */
        $hlp = Mage::helper(Config::CFG_HELPER);
        $result = $hlp->cfgRetailBonusEnabled();
        return $result;
    }
}