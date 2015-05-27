<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Block_Adminhtml_Own_Sales_Bonus_Collect_Payout_Index
    extends Praxigento_Bonus_Block_Adminhtml_Own_Sales_Bonus_Collect_Base
{
    private $_count = null;

    public function getCount()
    {
        if (is_null($this->_count)) {
            /** @var  $req Praxigento_Bonus_Model_Own_Service_Registry_Response_GetUnprocessedTransactionsCount */
            $req = Mage::getModel('prxgt_bonus_model/own_service_registry_request_getUnprocessedTransactionsCount');
            /** @var  $resp Praxigento_Bonus_Model_Own_Service_Registry_Response_GetUnprocessedTransactionsCount */
            $resp = $this->getRegistryCall()->getUnprocessedTransactions($req);
            $this->_count = $resp->getCount();
        }
        return $this->_count;

    }

}