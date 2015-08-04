<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;

include_once('../../phpunit_bootstrap.php');

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Test_Service_Period_Call_UnitTest extends PHPUnit_Framework_TestCase
{

    public function test_constructor()
    {
        $call = Mage::getModel(Config::CFG_SERVICE . '/period_call');
        $this->assertNotNull($call);
    }

    public function test_getPeriodForPersonalBonus()
    {
        /** @var  $call Praxigento_Bonus_Service_Period_Call */
        $call = Mage::getModel(Config::CFG_SERVICE . '/period_call');
        /** @var  $req  Praxigento_Bonus_Service_Period_Request_GetPeriodForPersonalBonus */
        $req = Mage::getModel(Config::CFG_SERVICE . '/period_request_getPeriodForPersonalBonus');
        $req->bonusTypeId = 1;
        $req->operationTypeIds = array(1, 2, 3);
        $req->periodCode = 'DAY';
        $req->periodTypeId = 3;
        /** @var  $resp Praxigento_Bonus_Service_Period_Response_GetPeriodForPersonalBonus */
        $resp = $call->getPeriodForPersonalBonus($req);
        $this->assertFalse($resp->isSucceed());

    }
}