<?php

namespace Quicko\Clubmanager\Utils;

use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class BackendUriHelper
{
  public static function createEditUrl(string $tableName, string $returnPathInfo, int $uid, int $pid = -1): string
  {
    $edit_url = '';
    /** @var UriBuilder $backendUriBuilder */
    $backendUriBuilder = GeneralUtility::makeInstance(UriBuilder::class);

    $return_url = (string) $backendUriBuilder->buildUriFromRoute($returnPathInfo, $pid == -1 ? [] : ['id' => $pid]);
    $params = [
      'edit' => [$tableName => [$uid => 'edit']],
      'returnUrl' => $return_url,
    ];
    $edit_url = (string) $backendUriBuilder->buildUriFromRoute('record_edit', $params);

    return $edit_url;
  }

  public static function createAddUrl(string $tableName, string $returnPathInfo, int $pid): string
  {
    $edit_url = '';
    /** @var UriBuilder $backendUriBuilder */
    $backendUriBuilder = GeneralUtility::makeInstance(UriBuilder::class);

    $return_url = (string) $backendUriBuilder->buildUriFromRoute($returnPathInfo, ['id' => $pid]);
    $params = [
      'edit' => [
        $tableName => [
          $pid => 'new',
        ],
      ],
      'returnUrl' => $return_url,
    ];
    $edit_url = (string) $backendUriBuilder->buildUriFromRoute('record_edit', $params);

    return $edit_url;
  }

  public static function createUpdateUrl(): string
  {
    /** @var UriBuilder $backendUriBuilder */
    $backendUriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
    $updateUrl = (string) $backendUriBuilder->buildUriFromRoute('tce_db', []);

    return $updateUrl;
  }

  public static function getAjaxUriTemplateDelete(): string
  {
    return self::createUpdateUrl();
  }

  public static function getPageUriTemplateNewEdit(): string
  {
    /** @var UriBuilder $urib */
    $urib = GeneralUtility::makeInstance(UriBuilder::class);
    $result = (string) $urib->buildUriFromRoute('record_edit', [
      'edit' => [],
    ]);

    return $result;
  }

  public static function getPageUriModule(string $moduleRoute, int $pid): string
  {
    /** @var UriBuilder $urib */
    $urib = GeneralUtility::makeInstance(UriBuilder::class);
    $uri = (string) $urib->buildUriFromRoute($moduleRoute, ['id' => $pid]);

    return $uri;
  }
}
