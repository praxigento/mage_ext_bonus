<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Model_Own_Source_Weekday {
    const FRIDAY = 'friday';
    const MONDAY = 'monday';
    const SATURDAY = 'saturday';
    const SUNDAY = 'sunday';
    const THURSDAY = 'thursday';
    const TUESDAY = 'tuesday';
    const WEDNESDAY = 'wednesday';

    public function toOptionArray() {
        $hlp    = Config::get()->helper();
        $result = array(
            array(
                'value' => self::SUNDAY,
                'label' => $hlp->__('Sunday')
            ), array(
                'value' => self::MONDAY,
                'label' => $hlp->__('Monday')
            ), array(
                'value' => self::TUESDAY,
                'label' => $hlp->__('Tuesday')
            ), array(
                'value' => self::WEDNESDAY,
                'label' => $hlp->__('Wednesday')
            ), array(
                'value' => self::THURSDAY,
                'label' => $hlp->__('Thursday')
            ), array(
                'value' => self::FRIDAY,
                'label' => $hlp->__('Friday')
            ), array(
                'value' => self::SATURDAY,
                'label' => $hlp->__('Saturday')
            )
        );
        return $result;
    }
}