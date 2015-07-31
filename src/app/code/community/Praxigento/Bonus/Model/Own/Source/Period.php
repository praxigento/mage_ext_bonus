<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Model_Own_Source_Period
{

    public function toOptionArray()
    {
        $result = array(
            array(
                'value' => Praxigento_Bonus_Config::PERIOD_DAY,
                'label' => Mage::helper('prxgt_bonus_helper')->__('Daily')
            ),
            array(
                'value' => Praxigento_Bonus_Config::PERIOD_WEEK,
                'label' => Mage::helper('prxgt_bonus_helper')->__('Weekly')
            ),
            array(
                'value' => Praxigento_Bonus_Config::PERIOD_MONTH,
                'label' => Mage::helper('prxgt_bonus_helper')->__('Monthly')
            ),
            array(
                'value' => Praxigento_Bonus_Config::PERIOD_YEAR,
                'label' => Mage::helper('prxgt_bonus_helper')->__('Yearly')
            )
        );
        return $result;
    }
}