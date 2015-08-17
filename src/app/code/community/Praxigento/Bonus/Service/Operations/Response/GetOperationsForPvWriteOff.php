<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Service_Operations_Response_GetOperationsForPvWriteOff
    extends Praxigento_Bonus_Service_Base_Response
{
    private $_collection;

    /**
     * @return mixed
     */
    public function getCollection()
    {
        return $this->_collection;
    }

    /**
     * @param mixed $collection
     */
    public function setCollection($collection)
    {
        $this->_collection = $collection;
    }

    public function isSucceed()
    {
        $result = ($this->getErrorCode() == self::ERR_NO_ERROR);
        return $result;
    }

}