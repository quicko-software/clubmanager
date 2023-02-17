<?php

defined('TYPO3') or die('Access denied.');

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use Quicko\Clubmanager\Utils\BackendModuleHelper;

(function () {

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

  BackendModuleHelper::addAdvertisingModule("memberlist");
  BackendModuleHelper::addAdvertisingModule("mailtasks");
  BackendModuleHelper::addAdvertisingModule("settlements");
  BackendModuleHelper::addAdvertisingModule("events");
  BackendModuleHelper::addAdvertisingModule("membershipstatistics");

})();
