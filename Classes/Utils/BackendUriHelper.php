<?php

namespace Quicko\Clubmanager\Utils;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Backend\Routing\UriBuilder;

class BackendUriHelper
{
  public static function createEditUrl($tableName, string $returnPathInfo, int $uid, int $pid = -1): string
  {
    $edit_url = '';
    $backendUriBuilder = GeneralUtility::makeInstance(UriBuilder::class);

    $return_url = (string) $backendUriBuilder->buildUriFromRoute($returnPathInfo, $pid == -1 ? [] : ['id' => $pid]);
    $params = [
      'edit' => [$tableName => [$uid => 'edit']],
      'returnUrl' => $return_url,
    ];
    $edit_url = (string) $backendUriBuilder->buildUriFromRoute('record_edit', $params);
    return $edit_url;
  }

  public static function createAddUrl($tableName, string $returnPathInfo, int $pid): string
  {
    $edit_url = '';
    $backendUriBuilder = GeneralUtility::makeInstance(UriBuilder::class);

    $return_url = (string) $backendUriBuilder->buildUriFromRoute($returnPathInfo, ['id' => $pid]);
    $params = [
      'edit' =>
      [
        $tableName =>
        [
          $pid => 'new'
        ]
      ],
      'returnUrl' => $return_url,
    ];
    $edit_url = (string) $backendUriBuilder->buildUriFromRoute('record_edit', $params);
    return $edit_url;
  }

  public static function createUpdateUrl(): string
  {
    $backendUriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
    $updateUrl = (string)$backendUriBuilder->buildUriFromRoute('tce_db', [ ]);
    return $updateUrl;
  }

  

  public static function getAjaxUriTemplateDelete(): string {
    return self::createUpdateUrl();
  }

  public static function getPageUriTemplateNewEdit(): string {
    $urib = GeneralUtility::makeInstance(UriBuilder::class);
    $result = (string) $urib->buildUriFromRoute('record_edit', [
      'edit' => []
    ]);
    return $result;
  }

  public static function getPageUriModule($moduleRoute, $pid): string {
    $urib = GeneralUtility::makeInstance(UriBuilder::class);
    $uri = (string) $urib->buildUriFromRoute($moduleRoute, ['id' => $pid]);
    return $uri;
  }
}
