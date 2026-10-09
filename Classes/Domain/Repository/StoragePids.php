<?php

namespace Quicko\Clubmanager\Domain\Repository;

use Quicko\Clubmanager\Utils\Typo3Mode;
use Quicko\Clubmanager\Utils\SettingUtils;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

// /
// / Get the page id where data model objects shall be stored to or retrieved from.
// /
class StoragePids
{
  public static function getList(?string $extensionName = null): array
  {
    if (Typo3Mode::isFrontend()) {
      /** @var ConfigurationManager $configurationManager */
      $configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
      foreach ($extensionName === null ? [null, 'Clubmanager'] : [$extensionName] as $configurationExtensionName) {
        $frameworkConfiguration = $configurationManager->getConfiguration(
          ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
          $configurationExtensionName
        );

        $storagePids = GeneralUtility::intExplode(',', (string)($frameworkConfiguration['persistence']['storagePid'] ?? ''));
        if ($storagePids !== [] && $storagePids !== [0]) {
          return $storagePids;
        }
      }
    }
    /** @var ExtensionConfiguration $extConf */
    $extConf = GeneralUtility::makeInstance(ExtensionConfiguration::class);
    $storagePidString = (string)$extConf->get(
      'clubmanager',
      'storagePid'
    );

    return GeneralUtility::intExplode(',', $storagePidString);
  }

  public static function getFirst(?string $extensionName = null): mixed
  {
    $list = self::getList($extensionName);

    return $list[0] ?? 0;
  }

  /**
   * Gets a site-specific storage page and falls back to the primary member storage page.
   */
  public static function getConfiguredStoragePid(string $siteSettingName): int
  {
    $memberStoragePid = (int) self::getFirst();
    if ($memberStoragePid <= 0) {
      return 0;
    }

    $configuredStoragePid = (int) SettingUtils::getSiteSetting(
      $memberStoragePid,
      $siteSettingName,
      0
    );

    return $configuredStoragePid > 0 ? $configuredStoragePid : $memberStoragePid;
  }
}
