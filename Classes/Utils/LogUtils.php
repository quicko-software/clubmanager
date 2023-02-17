<?php

namespace Quicko\Clubmanager\Utils;

use \TYPO3\CMS\Core\Log\LogLevel;
use \TYPO3\CMS\Core\Log\LogManager;
use \TYPO3\CMS\Core\Log\Writer\FileWriter;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Core\Core\Environment;


class LogUtils {

  const EMERGENCY = LogLevel::EMERGENCY;
  const ALERT = LogLevel::ALERT;
  const CRITICAL = LogLevel::CRITICAL;
  const ERROR = LogLevel::ERROR;
  const WARNING = LogLevel::WARNING;
  const NOTICE = LogLevel::NOTICE;
  const INFO = LogLevel::INFO;
  const DEBUG = LogLevel::DEBUG;

  public static function registerFileLogging() {
    $GLOBALS['TYPO3_CONF_VARS']['LOG']['Quicko']['Clubmanager']['writerConfiguration'] = [
      LogLevel::DEBUG => [
          FileWriter::class => [
              'logFile' => Environment::getVarPath() . '/log/typo3_Quicko_Clubmanager.log'
          ]
      ]
   ];    
  }

  public static function emergency($className, $msg) {
    self::log($className, self::EMERGENCY, $msg);
  }
  public static function alert($className, $msg) {
    self::log($className, self::ALERT, $msg); 
  }
  public static function critical($className, $msg) {
    self::log($className, self::CRITICAL, $msg);
  }
  public static function error($className, $msg) {
    self::log($className, self::ERROR, $msg);
  }
  public static function warning($className, $msg) {
    self::log($className, self::WARNING, $msg);
  }
  public static function notice($className, $msg) {
    self::log($className, self::NOTICE, $msg);
  }
  public static function info($className, $msg) {
    self::log($className, self::INFO, $msg);
  }
  public static function debug($className, $msg) {
    self::log($className, self::DEBUG, $msg);
  }

  private static $logger = null;

  private static function log($className, $logLevel, $msg) {
    self::getLogger($className)->log($logLevel, $msg);
  }

  private static function getLogger($className) {
    if (LogUtils::$logger === null) {
      LogUtils::$logger = GeneralUtility::makeInstance(LogManager::class)->getLogger($className);
    }
    return LogUtils::$logger;
  }
}
