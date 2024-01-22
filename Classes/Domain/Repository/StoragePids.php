<?php

namespace Quicko\Clubmanager\Domain\Repository;

use Quicko\Clubmanager\Utils\Typo3Mode;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

// /
// / Get the page id where data model objects shall be stored to or retrieved from.
// /
class StoragePids
{
  public static function getList(string $extensionName = null): array
  {
    if (Typo3Mode::isFrontend()) {
      /** @var ConfigurationManager $configurationManager */
      $configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
      $frameworkConfiguration = $configurationManager->getConfiguration(
        ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
        $extensionName
      );

      return GeneralUtility::intExplode(',', $frameworkConfiguration['persistence']['storagePid'] ?? '0');
    }
    /** @var ExtensionConfiguration $extConf */
    $extConf = GeneralUtility::makeInstance(ExtensionConfiguration::class);
    $storagePidString = $extConf->get(
      'clubmanager',
      'storagePid'
    );
    $storagePidInt = intval($storagePidString);

    return [$storagePidInt];
  }

  public static function getFirst(string $extensionName = null): mixed
  {
    $list = self::getList($extensionName);

    return $list[0];
  }
}
