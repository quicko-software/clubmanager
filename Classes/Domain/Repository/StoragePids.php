<?php

namespace Quicko\Clubmanager\Domain\Repository;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

use Quicko\Clubmanager\Utils\Typo3Mode;

///
/// Get the page id where data model objects shall be stored to or retrieved from.
///
class StoragePids
{
  public static function getList(?string $extensionName = null) {
    if (Typo3Mode::isFrontend()) {
      $configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
      $frameworkConfiguration = $configurationManager->getConfiguration(
        ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
        $extensionName
      );
      return GeneralUtility::intExplode(',', $frameworkConfiguration['persistence']['storagePid'] ?? '0');
    }
    $extConf = GeneralUtility::makeInstance(ExtensionConfiguration::class);
    $storagePidString = $extConf->get(
      'clubmanager',
      'storagePid'
    );
    $storagePidInt = intval($storagePidString);
    return array($storagePidInt);
  }

  public static function getFirst(?string $extensionName = null) {
    $list = self::getList($extensionName);
    return $list[0];
  }
}
