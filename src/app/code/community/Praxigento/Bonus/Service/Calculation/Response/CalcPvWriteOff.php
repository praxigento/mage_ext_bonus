<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Service_Calculation_Response_CalcPvWriteOff
    extends Praxigento_Bonus_Service_Base_Response
{
    public function isSucceed()
    {
        $result = ($this->getErrorCode() == self::ERR_NO_ERROR);
        return $result;
    }

}