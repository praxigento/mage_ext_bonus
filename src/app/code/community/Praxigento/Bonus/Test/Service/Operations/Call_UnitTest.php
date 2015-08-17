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
class Praxigento_Bonus_Test_Service_Operations_Call_UnitTest extends PHPUnit_Framework_TestCase
{

    public function test_constructor()
    {
        /** @var  $call Praxigento_Bonus_Service_Operations_Call */
        $call = Config::get()->serviceOperations();
        $this->assertNotNull($call);
    }

    public function test_getOperationsForPvWriteOff()
    {
        /**
         * Compose service.
         */
        /** @var  $mockCall Praxigento_Bonus_Service_Operations_Call */
        $mockCall = $this->mockCall(null);
        $resp = $mockCall->getOperationsForPvWriteOff(null);
        $this->assertTrue($resp instanceof Praxigento_Bonus_Service_Operations_Response_GetOperationsForPvWriteOff);
    }


    /**
     * @param $methods array of methods to be mocked.
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function mockCall($methods)
    {
        $mockBuilder = $this->getMockBuilder('Praxigento_Bonus_Service_Operations_Call');
        $result = $mockBuilder
            ->setMethods($methods)
            ->getMock();
        return $result;
    }

}