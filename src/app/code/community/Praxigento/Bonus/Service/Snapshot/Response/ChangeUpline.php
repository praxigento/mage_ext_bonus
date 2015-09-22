<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Service_Snapshot_Response_ChangeUpline
    extends Praxigento_Bonus_Service_Base_Response {

    const ERR_PARENT_ALREADY_SET = 'new_parent_already_set';
    const ERR_PARENT_IS_THE_CUSTOMER = 'new_parent_is_the_customer_itself';
    const ERR_PARENT_IS_FROM_DOWNLINE = 'new_parent_is_in_downline';

    public function isSucceed() {
        $result = ($this->getErrorCode() == self::ERR_NO_ERROR);
        return $result;
    }
}