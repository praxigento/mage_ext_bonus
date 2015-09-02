<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Service_Operations_Response_CreateOperationPvWriteOff
    extends Praxigento_Bonus_Service_Base_Response {

    const ERR_FAILED = 'transaction_creation_is_failed';

    public function isSucceed() {
        $result = ($this->getErrorCode() == self::ERR_NO_ERROR);
        return $result;
    }

}