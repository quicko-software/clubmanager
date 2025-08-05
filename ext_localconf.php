<?php

defined('TYPO3') or exit;

use Quicko\Clubmanager\Controller\CitiesController;
use Quicko\Clubmanager\Configuration\ExtRecoveryConfiguration;
use Quicko\Clubmanager\Controller\LocationController;
use Quicko\Clubmanager\Controller\MemberController;
use Quicko\Clubmanager\Domain\Model\Plugin;
use Quicko\Clubmanager\Evaluation\BicEvaluation;
use Quicko\Clubmanager\Evaluation\IbanEvaluation;
use Quicko\Clubmanager\Utils\PluginRegisterFacade;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\FrontendLogin\Configuration\RecoveryConfiguration;

call_user_func(function () {
  $GLOBALS['TYPO3_CONF_VARS']['SYS']['routing']['aspects']['PersistedTester'] = Quicko\Clubmanager\Routing\Aspect\PersistedTester::class;
  $GLOBALS['TYPO3_CONF_VARS']['SYS']['routing']['aspects']['SanitizeValue'] = Quicko\Clubmanager\Routing\Aspect\SanitizeValue::class;
  $GLOBALS['TYPO3_CONF_VARS']['SYS']['routing']['aspects']['SanitizeValueUidMapper'] = Quicko\Clubmanager\Routing\Aspect\SanitizeValueUidMapper::class;

  $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals'][IbanEvaluation::class] = '';
  $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals'][BicEvaluation::class] = '';

  PluginRegisterFacade::definePlugin(new Plugin(
    'clubmanager',
    'CitiesList',
    [CitiesController::class => 'list'],
    [CitiesController::class => ''],
    'cities.svg',
    'clubmanager',
    'StorageSettings.xml',
  ));

  PluginRegisterFacade::definePlugin(new Plugin(
    'clubmanager',
    'City',
    [CitiesController::class => 'detail'],
    [CitiesController::class => ''],
    'cities.svg',
    'clubmanager',
    'StorageSettings.xml'
  ));

  PluginRegisterFacade::definePlugin(new Plugin(
    'clubmanager',
    'Member',
    [MemberController::class => 'detail'],
    [MemberController::class => ''],
    'member.svg',
    'clubmanager',
    'StorageSettings.xml'
  ));

  PluginRegisterFacade::definePlugin(new Plugin(
    'clubmanager',
    'MemberList',
    [MemberController::class => 'list'],
    [MemberController::class => ''],
    'member.svg',
    'clubmanager',
    'StorageSettings.xml'
  ));

  PluginRegisterFacade::definePlugin(new Plugin(
    'clubmanager',
    'Location',
    [LocationController::class => 'detail'],
    [LocationController::class => ''],
    'location.svg',
    'clubmanager',
    'StorageSettings.xml'
  ));

  PluginRegisterFacade::definePlugin(new Plugin(
    'clubmanager',
    'LocationList',
    [LocationController::class => 'list'],
    [LocationController::class => ''],
    'location.svg',
    'clubmanager',
    'StorageSettings.xml'
  ));

  PluginRegisterFacade::configureAllPlugins();

  $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['clubmanager_fe_user_email_hook'] = Quicko\Clubmanager\Hooks\CopyMemberEmailToFeuserHook::class;
  $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['clubmanager_fe_user_password_hook'] = Quicko\Clubmanager\Hooks\ResetFeuserPasswordHook::class;
  $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['clubmanager_location_lat_lng_update_hook'] = Quicko\Clubmanager\Hooks\LocationLatLngUpdateHook::class;
  $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['clubmanager_member_starttime_hook'] = Quicko\Clubmanager\Hooks\MemberStartTimeHook::class;
  // $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['clubmanager_reset_password'] = \Quicko\Clubmanager\Hooks\EmailVerificationTokenResetHook::class;

  $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1454581922] = [
      'nodeName' => 'SearchLocation',
      'priority' => '70',
      'class' => Quicko\Clubmanager\FormEngine\SearchLocationButton::class,
  ];

  $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1738654663] = [
    'nodeName' => 'PasswordReset',
    'priority' => 30,
    'class' => Quicko\Clubmanager\FormEngine\PasswordReset::class,
 ];

  $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][RecoveryConfiguration::class] = [
   'className' => ExtRecoveryConfiguration::class,
  ];

  $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][Quicko\Clubmanager\Tasks\MemberLoginReminderTask::class] = [
      'extension' => 'clubmanager',
      'title' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_be.xlf:task.MemberLoginReminderTask.title',
      'description' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_be.xlf:task.MemberLoginReminderTask.description',
      'additionalFields' => Quicko\Clubmanager\Tasks\MemberLoginReminderTaskAdditionalFieldProvider::class,
  ];

  $extConf = GeneralUtility::makeInstance(ExtensionConfiguration::class);
  $fe_users_storagePid = $extConf->get(
    'clubmanager',
    'feUsersStoragePid'
  );

  ExtensionManagementUtility::addPageTSConfig(
    'TCAdefaults.fe_users.pid = ' . $fe_users_storagePid
  );

  $settings = $extConf->get('clubmanager');
  foreach ($settings as $key => $setting) {
    if ($key == 'storagePid') {
      ExtensionManagementUtility::addTypoScriptConstants(
        "plugin.tx_clubmanager.persistence.$key =" . $setting
      );
    } else {
      ExtensionManagementUtility::addTypoScriptConstants(
        "plugin.tx_clubmanager.settings.$key =" . $setting
      );
    }
  }
});
