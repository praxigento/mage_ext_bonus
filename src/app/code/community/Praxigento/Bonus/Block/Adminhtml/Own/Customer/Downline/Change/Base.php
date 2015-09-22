<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */

/**
 * User: Alex Gusev <alex@flancer64.com>
 * @method string getCurrentCustomerId()
 * @method null setCurrentCustomerId(string $value)
 * @method string getCurrentCustomerName()
 * @method null setCurrentCustomerName(string $value)
 * @method string getCurrentUplineId()
 * @method null setCurrentUplineId(string $value)
 * @method string getCurrentUplineName()
 * @method null setCurrentUplineName(string $value)
 * @method string getNewUplineId()
 * @method null setNewUplineId(string $value)
 * @method string getNewUplineName()
 * @method null setNewUplineName(string $value)
 * @method bool getIsFoundCurrentCustomer()
 * @method null setIsFoundCurrentCustomer(bool $value)
 * @method bool getIsFoundCurrentUpline()
 * @method null setIsFoundCurrentUpline(bool $value)
 * @method bool getIsFoundNewUpline()
 * @method null setIsFoundNewUpline(bool $value)
 * @method bool getIsErrorFound()
 * @method null setIsErrorFound(bool $value)
 * @method string getErrorMessage()
 * @method null setErrorMessage(string $value)
 */
class Praxigento_Bonus_Block_Adminhtml_Own_Customer_Downline_Change_Base
    extends Praxigento_Bonus_Block_Adminhtml_Own_Base {

    const DOM_FLD_CUSTOMER_ID = 'prxgtMlmId';
    const DOM_FLD_UPLINE_ID = 'prxgtMlmUplineNew';

    public function uiTitle() {
        echo $this->__('Customer Upline Change');
    }
}