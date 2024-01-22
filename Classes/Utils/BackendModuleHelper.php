<?php

namespace Quicko\Clubmanager\Utils;

use Quicko\Clubmanager\Controller\Backend\AdvertisingController;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

class BackendModuleHelper
{
  public static function removeAdertisingModule(string $moduleName): void
  {
    $key = 'Clubmanager' . ucfirst($moduleName);
    $fullkey = 'clubmanager_' . $key;

    unset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extbase']['extensions']['Clubmanager']['modules'][$fullkey]);
    unset($GLOBALS['TBE_MODULES']['_configuration'][$fullkey]);

    $clubmanager = $GLOBALS['TBE_MODULES']['clubmanager'];
    $clubmanagerEntries = explode(',', $clubmanager);
    if (($pos = array_search($key, $clubmanagerEntries)) !== false) {
      unset($clubmanagerEntries[$pos]);
    }
    $GLOBALS['TBE_MODULES']['clubmanager'] = implode(',', $clubmanagerEntries);
  }

  public static function addAdvertisingModule(string $moduleName): void
  {
    ExtensionUtility::registerModule(
      'clubmanager',
      'clubmanager',
      $moduleName,
      '',
      [
        AdvertisingController::class => $moduleName,
      ],
      [
        'access' => 'user,group',
        'icon' => "EXT:clubmanager/Resources/Public/Icons/be_mod_$moduleName.svg",
        'labels' => "LLL:EXT:clubmanager/Resources/Private/Language/locallang_be.xlf:mlang_tabs_tab_$moduleName",
        'navigationComponentId' => 'TYPO3/CMS/Backend/PageTree/PageTreeElement',
        'inheritNavigationComponentFromMainModule' => false,
      ]
    );
  }
}
