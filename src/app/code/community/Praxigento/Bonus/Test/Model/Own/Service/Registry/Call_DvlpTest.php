<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;
use Praxigento_Bonus_Model_Own_Order as BonusOrder;
use Praxigento_Bonus_Model_Own_Service_Base_Response as BaseResponse;
use Praxigento_Bonus_Model_Own_Service_Registry_Request_CreatePayouts as CreatePayoutsRequest;
use Praxigento_Bonus_Model_Own_Service_Registry_Request_CreateTransactions as CreateTransactionsRequest;
use Praxigento_Bonus_Model_Own_Service_Registry_Request_SaveRetailBonus as SaveRetailBonusRequest;
use Praxigento_Bonus_Model_Own_Service_Registry_Response_CreatePayouts as CreatePayoutsResponse;
use Praxigento_Bonus_Model_Own_Service_Registry_Response_CreateTransactions as CreateTransactionsResponse;
use Praxigento_Bonus_Model_Own_Service_Registry_Response_SaveRetailBonus as SaveRetailBonusResponse;

include_once('../../../../phpunit_bootstrap.php');

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Test_Model_Own_Service_Registry_Call_DvlpTest extends PHPUnit_Framework_TestCase
{
    public function test_saveRetailBonus()
    {
        $call = Mage::getModel('prxgt_bonus_model/own_service_registry_call');
        $req = Mage::getModel('prxgt_bonus_model/own_service_registry_request_saveRetailBonus');
        $order = Mage::getModel('sales/order')->load(6);
        $req->setOrder($order);
        $resp = $call->saveRetailBonus($req);
        $this->assertTrue($resp instanceof SaveRetailBonusResponse);
    }

    public function test_constructor()
    {
        $call = Mage::getModel('prxgt_bonus_model/own_service_registry_call');
        $this->assertNotNull($call);
    }

    public function test_createPayouts()
    {
        $call = Mage::getModel('prxgt_bonus_model/own_service_registry_call');
        /** @var  $req CreatePayoutsRequest */
        $req = Mage::getModel('prxgt_bonus_model/own_service_registry_request_createPayouts');
        $resp = $call->createPayouts($req);
        $this->assertTrue($resp instanceof CreatePayoutsResponse);
    }

    public function test_createTransactions()
    {
        $call = Mage::getModel('prxgt_bonus_model/own_service_registry_call');
        /** @var  $req CreateTransactionsRequest */
        $req = Mage::getModel('prxgt_bonus_model/own_service_registry_request_createTransactions');
        $resp = $call->createTransactions($req);
        $this->assertTrue($resp instanceof CreateTransactionsResponse);
    }


}