<?php

namespace Quicko\Clubmanager\Mail\Generator;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Quicko\Clubmanager\Mail\Generator\BaseMailGenerator;


class MailGeneratorFactory
{
  /**
   * @throws InvalidArgumentException
   */
  public static function createGenerator($className,$useCachedRepository = false) : BaseMailGenerator
  {
    /** @var BaseMailGenerator $instance */
    $instance = GeneralUtility::makeInstance($className,$useCachedRepository); 
    if (!is_subclass_of($instance, BaseMailGenerator::class)) {
      throw new \InvalidArgumentException('Invalid generator :' . $className, 1664524663);
    }
    return $instance;
  }
}
