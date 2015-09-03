<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */

/**
 * Logger should log events to Nmmmlm_Log (Log4PHP) logger or to Magento default logger.
 *
 * User: Alex Gusev <alex@flancer64.com>
 *
 * @codeCoverageIgnore
 *
 */
class Praxigento_Bonus_Logger {
    private static $_isLog4phpUsed = null;
    private $_loggerLog4php;
    private $_name;

    function __construct($name) {
        /**
         * switch off/on error reporting to prevent messages like
         * "ERR (3): Warning: include(Praxigento\Log\Logger.php): failed to open stream: No such file or directory"
         * in case of Praxigento_Log module is not used.
         */
        $level                = error_reporting(0);
        self::$_isLog4phpUsed = class_exists('Praxigento_Log_Logger', true);
        error_reporting($level);
        if(self::$_isLog4phpUsed) {
            $this->_loggerLog4php = Praxigento_Log_Logger::getLogger($name);
        } else {
            $this->_name = is_object($name) ? get_class($name) : (string)$name;
        }
    }

    /**
     * Override getter to use '$log = Praxigento_Log_Logger::getLogger($this)' form in Mage classes.
     * @static
     *
     * @param string $name
     *
     * @return Praxigento_Bonus_Logger
     */
    public static function getLogger($name) {
        $class = __CLASS__;
        return new $class($name);
    }

    public function debug($message, $throwable = null) {
        $this->doLog($message, $throwable, 'debug', Zend_Log::INFO);
    }

    public function error($message, $throwable = null) {
        $this->doLog($message, $throwable, 'error', Zend_Log::ERR);
    }

    public function fatal($message, $throwable = null) {
        $this->doLog($message, $throwable, 'fatal', Zend_Log::CRIT);
    }

    public function info($message, $throwable = null) {
        $this->doLog($message, $throwable, 'info', Zend_Log::NOTICE);
    }

    public function trace($message, $throwable = null) {
        $this->doLog($message, $throwable, 'trace', Zend_Log::DEBUG);
    }

    public function warn($message, $throwable = null) {
        $this->doLog($message, $throwable, 'warn', Zend_Log::WARN);
    }

    /**
     * Internal dispatcher for the called log method.
     *
     * @param $message
     * @param $throwable
     * @param $log4phpMethod
     * @param $zendLevel
     */
    private function doLog($message, $throwable, $log4phpMethod, $zendLevel) {
        if(self::$_isLog4phpUsed) {
            $this->_loggerLog4php->$log4phpMethod($message, $throwable);
        } else {
            Mage::log($this->_name . ': ' . $message, $zendLevel);
            if($throwable instanceof Exception) {
                Mage::logException($throwable);
            }
        }
    }
}