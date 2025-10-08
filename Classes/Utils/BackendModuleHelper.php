<?php

namespace Quicko\Clubmanager\Utils;

use Quicko\Clubmanager\Controller\Backend\AdvertisingController;

class BackendModuleHelper
{
  public static function createAdvertisingModuleDescripterV12(
    string $moduleName,
    string $beUrlSubPath
  ) : array {
    return [
      'parent' => 'clubmanager',
      'access' => 'user',
      'workspaces' => 'live',
      'path' => "/module/clubmanager/$beUrlSubPath",
      'labels' => "LLL:EXT:clubmanager/Resources/Private/Language/locallang_be.xlf:mlang_tabs_tab_$moduleName",
      'iconIdentifier' => "tx-clubmanager_icon-be_mod_$moduleName",
      'extensionName' => 'clubmanager',
      'controllerActions' => [
        AdvertisingController::class => [$moduleName]
      ],
    ];
  }

}
