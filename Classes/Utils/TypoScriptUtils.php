<?php

namespace Quicko\Clubmanager\Utils;

use RuntimeException;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\TypoScript\TemplateService;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;

class TypoScriptUtils
{
  public static function extractTypoScriptValue(array $typoScriptArray, string $dotPath): mixed
  {
    /** @var TypoScriptService $typoScriptService */
    $typoScriptService = GeneralUtility::makeInstance(TypoScriptService::class);
    $typoScriptSettingsWithoutDots = $typoScriptService->convertTypoScriptArrayToPlainArray($typoScriptArray);

    $pathParts = explode('.', $dotPath);
    $numParts = count($pathParts);
    $result = $typoScriptSettingsWithoutDots;
    for ($i = 0; $i < $numParts; ++$i) {
      $part = $pathParts[$i];
      if (!array_key_exists($part, $result)) {
        return null;
      }
      $result = $result[$part];
    }

    return $result;
  }

  public static function getTypoScriptValueForPage(string $dotPath, int $pageId): mixed
  {
    if (GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() > 12) {
      $typoScriptService = GeneralUtility::makeInstance(\Quicko\Clubmanager\Utils\UniversalTypoScriptService::class);
      $ts = $typoScriptService->getTypoScript($pageId);
      return TypoScriptUtils::extractTypoScriptValue($ts, $dotPath);
    }

    /** @var TemplateService $template */
    $template = GeneralUtility::makeInstance(TemplateService::class);
    // do not log time-performance information
    $template->tt_track = false;
    // Explicitly trigger processing of extension static files
    $template->setProcessExtensionStatics(true);
    // Get the root line
    $rootline = [];
    if ($pageId > 0) {
      try {
        /** @var RootlineUtility $rootlineUtility */
        $rootlineUtility = GeneralUtility::makeInstance(RootlineUtility::class, $pageId);
        $rootline = $rootlineUtility->get();
      } catch (RuntimeException $e) {
        $rootline = [];
      }
    }
    // This generates the constants/config + hierarchy info for the template.
    $template->runThroughTemplates($rootline, 0);
    $template->generateConfig();

    return TypoScriptUtils::extractTypoScriptValue($template->setup, $dotPath);
  }
}
