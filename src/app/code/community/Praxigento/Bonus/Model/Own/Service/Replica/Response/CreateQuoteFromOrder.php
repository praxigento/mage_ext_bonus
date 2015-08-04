<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Service_Replica_Response_CreateQuoteFromOrder
    extends Praxigento_Bonus_Service_Base_Response
{
    /** @var  Mage_Sales_Model_Quote */
    private $quote;

    /**
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return $this->quote;
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     */
    public function setQuote(Mage_Sales_Model_Quote $quote)
    {
        $this->quote = $quote;
    }

    public function isSucceed()
    {
        return false;
    }
}