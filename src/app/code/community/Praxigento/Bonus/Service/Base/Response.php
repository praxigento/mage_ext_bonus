<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */

/**
 * Base class for all services calls responses.
 * All response classes has getters only, setters are useless for responses.
 *
 * User: Alex Gusev <alex@flancer64.com>
 */
abstract class Praxigento_Bonus_Service_Base_Response {
    const ERR_BONUS_DISABLED = 'bonus_is_disabled';
    const ERR_NO_ERROR = 'no_error';
    const ERR_UNDEFINED = 'undefined';
    /** @var  string */
    private $_errorMessage;
    private $errorCode = self::ERR_UNDEFINED;

    /**
     * @return string
     */
    public function getErrorMessage() {
        return $this->_errorMessage;
    }

    /**
     * @param string $errorMessage
     */
    public function setErrorMessage($errorMessage) {
        $this->_errorMessage = $errorMessage;
    }

    /**
     * @return mixed
     */
    public function getErrorCode() {
        return $this->errorCode;
    }

    /**
     * @param mixed $errorCode
     */
    public function setErrorCode($errorCode) {
        $this->errorCode = $errorCode;
    }

    public abstract function isSucceed();
}