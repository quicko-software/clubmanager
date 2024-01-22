<?php

namespace Quicko\Clubmanager\Utils;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SettingUtils
{
  public static function get(string $extKey, string $propName): mixed
  {
    /** @var ExtensionConfiguration $extensionConfiguration */
    $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);

    return $extensionConfiguration->get($extKey, $propName);
  }
}
