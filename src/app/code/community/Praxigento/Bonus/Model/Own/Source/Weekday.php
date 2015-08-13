<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Model_Own_Source_Weekday
{
    const FRIDAY = 'friday';
    const MONDAY = 'monday';
    const SATURDAY = 'saturday';
    const SUNDAY = 'sunday';
    const THURSDAY = 'thursday';
    const TUESDAY = 'tuesday';
    const WEDNESDAY = 'wednesday';

    public function toOptionArray()
    {
        $result = array(
            array(
                'value' => self::SUNDAY,
                'label' => Config::helper()->__('Sunday')
            ), array(
                'value' => self::MONDAY,
                'label' => Config::helper()->__('Monday')
            ), array(
                'value' => self::TUESDAY,
                'label' => Config::helper()->__('Tuesday')
            ), array(
                'value' => self::WEDNESDAY,
                'label' => Config::helper()->__('Wednesday')
            ), array(
                'value' => self::THURSDAY,
                'label' => Config::helper()->__('Thursday')
            ), array(
                'value' => self::FRIDAY,
                'label' => Config::helper()->__('Friday')
            ), array(
                'value' => self::SATURDAY,
                'label' => Config::helper()->__('Saturday')
            )
        );
        return $result;
    }
}