<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Model_Own_Service_Registry_Response_GetBonusesToProcess
    extends Praxigento_Bonus_Model_Own_Service_Base_Response
{
    /**
     * Array of the bonus IDs to process.
     * @var array
     */
    private $_bonusIds = array();

    /**
     * @return array
     */
    public function getBonusIds()
    {
        return $this->_bonusIds;
    }

    /**
     * @param array $val
     */
    public function setBonusIds($val)
    {
        $this->_bonusIds = $val;
    }

    public function isSucceed()
    {
        $result = (is_array($this->_bonusIds)) && (count($this->_bonusIds));
        return $result;
    }
}