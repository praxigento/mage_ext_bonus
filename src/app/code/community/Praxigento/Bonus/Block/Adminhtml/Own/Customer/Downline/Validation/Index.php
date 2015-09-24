<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;
use Praxigento_Bonus_Model_Own_Snap_Downline as SnapDownline;

/**
 * User: Alex Gusev <alex@flancer64.com>
 *
 * @method string getFormTitle()
 * @method null setFormTitle(string $value)
 */
class Praxigento_Bonus_Block_Adminhtml_Own_Customer_Downline_Validation_Index
    extends Praxigento_Bonus_Block_Adminhtml_Own_Base {

    const DOM_SELECT = 'prxgtBonusSelectPeriod';

    private $_periodsAvailable;

    public function uiTitle() {
        echo $this->__('Downline Tree Validation');
    }

    public function getPeriodsAvailable() {
        if(is_null($this->_periodsAvailable)) {
            /** @var  $rsrc Mage_Core_Model_Resource */
            $rsrc = Config::get()->singleton('core/resource');
            $tbl = $rsrc->getTableName(Config::ENTITY_SNAP_DOWNLINE);
            $conn = $rsrc->getConnection('core_write');
            $col = SnapDownline::ATTR_PERIOD;
            $sql = "SELECT $col FROM $tbl GROUP BY $col ORDER BY $col DESC";
            $this->_periodsAvailable = $conn->fetchAll($sql);
        }
        return $this->_periodsAvailable;
    }

}