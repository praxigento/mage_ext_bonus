<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */

/**
 * Logger should log events to Nmmmlm_Log (Log4PHP) logger or to Magento default logger.
 *
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Logger
{
    private static $_isLog4phpUsed = null;
    private $_loggerLog4php;
    private $_name;

    function __construct($name)
    {
        if (is_null(self::$_isLog4phpUsed)) {
            $isClassExists = class_exists('Praxigento_Log_Logger', true);
            self::$_isLog4phpUsed = filter_var($isClassExists, FILTER_VALIDATE_BOOLEAN);
        }
        if (self::$_isLog4phpUsed) {
            $this->_loggerLog4php = Praxigento_Log_Logger::getLogger($name);
        } else {
            $this->_name = is_object($name) ? get_class($name) : (string)$name;
        }
    }

    /**
     * @return bool
     */
    public static function isLog4phpUsed()
    {
        return self::$_isLog4phpUsed;
    }

    public static function getLogger($name)
    {
        $class = __CLASS__;
        return new $class($name);
    }

    public function debug($message, $throwable = null)
    {
        $this->doLog($message, $throwable, 'debug', Zend_Log::INFO);
    }

    private function doLog($message, $throwable, $log4phpMethod, $zendLevel)
    {
        if (self::$_isLog4phpUsed) {
            $this->_loggerLog4php->$log4phpMethod($message, $throwable);
        } else {
            Mage::log($this->_name . ': ' . $message, $zendLevel);
            if ($throwable instanceof Exception) {
                Mage::logException($throwable);
            }
        }
    }

    public function error($message, $throwable = null)
    {
        $this->doLog($message, $throwable, 'error', Zend_Log::ERR);
    }

    public function fatal($message, $throwable = null)
    {
        $this->doLog($message, $throwable, 'fatal', Zend_Log::CRIT);
    }

    public function info($message, $throwable = null)
    {
        $this->doLog($message, $throwable, 'info', Zend_Log::NOTICE);
    }

    public function trace($message, $throwable = null)
    {
        $this->doLog($message, $throwable, 'trace', Zend_Log::DEBUG);
    }

    public function warn($message, $throwable = null)
    {
        $this->doLog($message, $throwable, 'warn', Zend_Log::WARN);
    }
}