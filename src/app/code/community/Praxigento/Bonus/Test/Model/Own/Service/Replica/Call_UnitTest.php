<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Model_Own_Service_Replica_Request_CreateQuoteFromOrder as CreateQuoteFromOrderRequest;
use Praxigento_Bonus_Model_Own_Service_Replica_Response_CreateQuoteFromOrder as CreateQuoteFromOrderResponse;

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
include_once('../../../../phpunit_bootstrap.php');

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Test_Model_Own_Service_Replica_Call_UnitTest extends PHPUnit_Framework_TestCase
{

    public function test_createQuoteFromOrder()
    {
        $call = Mage::getModel('prxgt_bonus_model/own_service_replica_call');
        $req = Mage::getModel('prxgt_bonus_model/own_service_replica_request_createQuoteFromOrder');
        $req->setCustomerId(6);
        $req->setOrderId(91);
        $resp = $call->createQuoteFromOrder($req);
        $this->assertTrue($resp instanceof CreateQuoteFromOrderResponse);
    }

    public function test_constructor()
    {
        $call = Mage::getModel('prxgt_bonus_model/own_service_replica_call');
        $this->assertNotNull($call);
    }
}