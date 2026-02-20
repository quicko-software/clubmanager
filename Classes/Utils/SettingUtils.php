<?php

namespace Quicko\Clubmanager\Utils;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Utility class for accessing Extension Configuration and Site Settings
 */
class SettingUtils
{
  /**
   * Get Extension Configuration value
   */
  public static function get(string $extKey, string $propName): mixed
  {
    /** @var ExtensionConfiguration $extensionConfiguration */
    $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);

    return $extensionConfiguration->get($extKey, $propName);
  }

  /**
   * Get Site Setting value (TYPO3 v13 Site Sets pattern)
   * 
   * @param int $pageId Page id to determine the site
   * @param string $key Setting key using dot notation (e.g., 'clubmanager.memberJournalStoragePid')
   * @param mixed $default Default value if not configured or site not found
   * @return mixed Setting value or default
   */
  public static function getSiteSetting(int $pageId, string $key, mixed $default = null): mixed
  {
    if ($pageId <= 0) {
      return $default;
    }

    try {
      $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
      $site = $siteFinder->getSiteByPageId($pageId);
      return $site->getSettings()->get($key, $default);
    } catch (SiteNotFoundException $e) {
      return $default;
    }
  }
}
