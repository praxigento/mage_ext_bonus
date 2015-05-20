<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Model_Own_Service_Registry_Request_SaveRetailBonus as SaveRetailBonusRequest;
use Praxigento_Bonus_Model_Own_Service_Registry_Response_SaveRetailBonus as SaveRetailBonusResponse;

include_once('../../../../phpunit_bootstrap.php');

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Test_Model_Own_Service_Registry_Call_DvlpTest extends PHPUnit_Framework_TestCase
{
    public function test_createQuoteFromOrder()
    {
        $call = Mage::getModel('prxgt_bonus_model/own_service_registry_call');
        $req = Mage::getModel('prxgt_bonus_model/own_service_registry_request_saveRetailBonus');
        $order = Mage::getModel('sales/order')->load(104);
        $req->setOrder($order);
        $resp = $call->saveRetailBonus($req);
        $this->assertTrue($resp instanceof SaveRetailBonusResponse);
    }

    public function test_constructor()
    {
        $call = Mage::getModel('prxgt_bonus_model/own_service_registry_call');
        $this->assertNotNull($call);
    }
}