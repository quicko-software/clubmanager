<?php

namespace Quicko\Clubmanager\Mail\Generator;

use InvalidArgumentException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class MailGeneratorFactory
{
  /**
   * @throws InvalidArgumentException
   */
  public static function createGenerator(string $className, bool $useCachedRepository = false): BaseMailGenerator
  {
    $instance = GeneralUtility::makeInstance($className, $useCachedRepository);
    if (!is_subclass_of($instance, BaseMailGenerator::class)) {
      throw new InvalidArgumentException('Invalid generator :' . $className, 1664524663);
    }

    return $instance;
  }
}
