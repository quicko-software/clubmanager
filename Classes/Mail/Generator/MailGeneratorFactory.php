<?php

namespace Quicko\Clubmanager\Mail\Generator;

use InvalidArgumentException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class MailGeneratorFactory
{
  /**
   * @template T of object
   *
   * @param class-string<T> $className
   *
   * @throws InvalidArgumentException
   */
  public static function createGenerator(string $className, bool $useCachedRepository = false): BaseMailGenerator
  {
    if (!class_exists($className)) {
      throw new InvalidArgumentException('Generator class not found: ' . $className, 1771254301);
    }

    $instance = GeneralUtility::makeInstance($className, $useCachedRepository);

    if (!is_subclass_of($instance, BaseMailGenerator::class)) {
      throw new InvalidArgumentException('Invalid generator :' . $className, 1664524663);
    }

    return $instance;
  }
}
