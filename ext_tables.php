<?php

defined('TYPO3') or die('Access denied.');

use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Quicko\Clubmanager\Utils\BackendModuleHelper;

(function () {
  if (GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() < 12) { // otherwise regisered via /Configuration/Backend/Modules.php
    //
    // Adds the backend main-module (collapsable group), which remains
    // invisible until depending extensions add their sub-modules into it.
    //
    ExtensionManagementUtility::addModule(
      'clubmanager',
      '',
      '',
      null,
      [
        'name' => 'clubmanager',
        'labels' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_be.xlf:backend_main_module_name',
        'icon' => "EXT:clubmanager/Resources/Public/Icons/be_mod_clubmanager3.svg",
      ]
    );

  }
})();
