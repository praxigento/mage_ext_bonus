<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Block_Adminhtml_Own_Sales_Bonus_Payment_Index
    extends Praxigento_Bonus_Block_Adminhtml_Own_Sales_Bonus_Base {
    private $_count = null;

    public function getCount() {
        if(is_null($this->_count)) {
            /** @var  $req Praxigento_Bonus_Model_Own_Service_Registry_Response_GetUnprocessedPayoutsCount */
            $req = Mage::getModel('prxgt_bonus_model/service_registry_request_getUnprocessedPayoutsCount');
            /** @var  $resp Praxigento_Bonus_Model_Own_Service_Registry_Response_GetUnprocessedPayoutsCount */
            $resp         = $this->getRegistryCall()->getUnprocessedPayoutsCount($req);
            $this->_count = $resp->getCount();
        }
        return $this->_count;

    }
}