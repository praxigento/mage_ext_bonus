<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;

/**
 * Process orders and calculate retail bonuses.
 *
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Adminhtml_Own_Sales_Bonus_Collect_RetailController extends Mage_Adminhtml_Controller_Action
{
    protected function _construct()
    {
        parent::_construct();
        $this->_setTitile();
    }

    private function _setTitile()
    {
        $this->_title($this->__('Sales'))->_title($this->__('Retail Bonus'))->_title($this->__('Collect'));
    }

    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function postAction()
    {
        $this->loadLayout();
        /** @var  $hlp Praxigento_Bonus_Helper_Data */
        $hlp = Mage::helper(Config::CFG_HELPER);
        /** @var  $block Praxigento_Bonus_Block_Adminhtml_Own_Sales_Bonus_Collect_Retail_Post */
        $block = Mage::app()->getLayout()->getBlock('prxgt_bonus_sales_bonus_collect_post');
        /* process orders */
        if ($hlp->cfgRetailBonusEnabled()) {
            /* prevent memory exhausting */
            ini_set('memory_limit', '-1');
            /* process orders */
            $srv = Mage::getModel('prxgt_bonus_model/own_service_registry_call');
            $orderIds = $this->_getUnprocessedOrders();
            $processed = 0;
            $failed = array();
            foreach ($orderIds as $one) {
                $id = $one['entity_id'];
                $order = Mage::getModel('sales/order')->load($id);
                $req = Mage::getModel('prxgt_bonus_model/own_service_registry_request_saveRetailBonus');
                $req->setOrder($order);
                try {
                    $resp = $srv->saveRetailBonus($req);
                    if ($resp->isSucceed()) {
                        $processed++;
                    }
                } catch (Exception $e) {
                    $msg = $e->getMessage();
                    /** @var  $log Praxigento_Bonus_Logger */
                    $log = Praxigento_Bonus_Logger::getLogger(__CLASS__);
                    $log->error("Cannot create retail bonus for order #$id. Error: $msg");
                    $failed[$id] = $msg;
                }
            }
            /* populate UI block */
            $block->setProcessedCount($processed);
            $block->setFailedOrders($failed);
        }
        $this->renderLayout();
    }

    private function _getUnprocessedOrders()
    {
        /** @var  $rsrc Mage_Core_Model_Resource */
        $rsrc = Mage::getSingleton('core/resource');
        /** @var  \Varien_Db_Adapter_Pdo_Mysql */
        $conn = $rsrc->getConnection('core_write');
        $tblSales = $rsrc->getTableName('sales/order');
        $tblRetail = $rsrc->getTableName(Config::CFG_MODEL . '/' . Config::CFG_ENTITY_ORDER);
        $query = "
SELECT $tblSales.entity_id FROM $tblSales
  LEFT OUTER JOIN $tblRetail ON $tblSales.entity_id = $tblRetail.order_id
WHERE $tblRetail.order_id IS NULL";
        $rs = $conn->query($query);
        $result = $rs->fetchAll();
        return $result;
    }
}