<?php

namespace Quicko\Clubmanager\Utils;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Log\Writer\FileWriter;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class LogUtils
{
  public const EMERGENCY = LogLevel::EMERGENCY;
  public const ALERT = LogLevel::ALERT;
  public const CRITICAL = LogLevel::CRITICAL;
  public const ERROR = LogLevel::ERROR;
  public const WARNING = LogLevel::WARNING;
  public const NOTICE = LogLevel::NOTICE;
  public const INFO = LogLevel::INFO;
  public const DEBUG = LogLevel::DEBUG;

  public static function registerFileLogging(): void
  {
    $GLOBALS['TYPO3_CONF_VARS']['LOG']['Quicko']['Clubmanager']['writerConfiguration'] = [
      LogLevel::DEBUG => [
          FileWriter::class => [
              'logFile' => Environment::getVarPath() . '/log/typo3_Quicko_Clubmanager.log',
          ],
      ],
   ];
  }

  public static function emergency(string $className, string $msg): void
  {
    self::log($className, self::EMERGENCY, $msg);
  }

  public static function alert(string $className, string $msg): void
  {
    self::log($className, self::ALERT, $msg);
  }

  public static function critical(string $className, string $msg): void
  {
    self::log($className, self::CRITICAL, $msg);
  }

  public static function error(string $className, string $msg): void
  {
    self::log($className, self::ERROR, $msg);
  }

  public static function warning(string $className, string $msg): void
  {
    self::log($className, self::WARNING, $msg);
  }

  public static function notice(string $className, string $msg): void
  {
    self::log($className, self::NOTICE, $msg);
  }

  public static function info(string $className, string $msg): void
  {
    self::log($className, self::INFO, $msg);
  }

  public static function debug(string $className, string $msg): void
  {
    self::log($className, self::DEBUG, $msg);
  }

  private static ?Logger $logger = null;
  
  private static function log(string $className, string $logLevel, string $msg): void
  {
    self::getLogger($className)->log($logLevel, $msg);
  }

  private static function getLogger(string $className): Logger
  {
    if (LogUtils::$logger === null) {
      /** @var LogManager $logManager */
      $logManager = GeneralUtility::makeInstance(LogManager::class);
      LogUtils::$logger = $logManager->getLogger($className);
    }

    return LogUtils::$logger;
  }
}
