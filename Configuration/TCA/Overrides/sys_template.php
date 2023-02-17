<?php

defined('TYPO3') or die();
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

call_user_func(function () {
  ExtensionManagementUtility::addStaticFile("clubmanager", "Configuration/TypoScript", "Clubmanager");
  ExtensionManagementUtility::addStaticFile("clubmanager", "Configuration/TypoScript/Cookieman", "Clubmanager Cookieman");
  ExtensionManagementUtility::addStaticFile("clubmanager", "Configuration/TypoScript/Felogin", "Clubmanager Felogin");
});
