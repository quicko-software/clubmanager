<?php

namespace Quicko\Clubmanager\Utils;

use Quicko\Clubmanager\Domain\Model\Plugin;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

class PluginRegisterFacade
{
  protected static array $pluginsToConfigure = [];
  protected static array $pluginsToRegister = [];

  public static function configureAllPlugins(): void
  {
    /** @var Plugin $plugin */
    foreach (self::$pluginsToConfigure as $plugin) {
      ExtensionUtility::configurePlugin(
        $plugin->getExtensionKey(),
        $plugin->getPluginName(),
        $plugin->getControllerActions(),
        $plugin->getNonCacheableControllerActions(),
        $plugin->getPluginType()
      );
      self::addToWizard($plugin);
    }
    self::$pluginsToConfigure = [];
  }

  private static function addToWizard(Plugin $plugin): void
  {
    $wizardGroupId = $plugin->getWizardGroupId();
    if (empty($wizardGroupId)) {
      return;
    }
    $underscoreName = GeneralUtility::camelCaseToLowerCaseUnderscored($plugin->getExtensionKey());

    $speakingName = 'LLL:EXT:' . $underscoreName . '/Resources/Private/Language/locallang_be.xlf:content_element.' . self::getPluginId($plugin);
    $speakingDescription = 'LLL:EXT:' . $underscoreName . '/Resources/Private/Language/locallang_be.xlf:content_element.' . self::getPluginId($plugin) . '.description';
    $list_type = self::getPluginSignature($plugin);
    $iconIdentifier = 'ext-' . $underscoreName . '-content-' . self::getPluginId($plugin) . '-icon';

    $tsconfig = <<<EOS
    mod.wizards.newContentElement.wizardItems.$wizardGroupId {
      elements {
          $list_type {
              iconIdentifier = $iconIdentifier
              title = $speakingName
              description = $speakingDescription
              tt_content_defValues {
                  CType = list
                  list_type = $list_type
              }
          }   
      }
      show := addToList($list_type)
    }
    EOS;
    ExtensionManagementUtility::addPageTSConfig($tsconfig);

    /** @var IconRegistry $iconRegistry */
    $iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);
    $iconRegistry->registerIcon(
      $iconIdentifier,
      SvgIconProvider::class,
      ['source' => self::getIconFilePath($plugin)]
    );
  }

  public static function registerAllPlugins(): void
  {
    /** @var Plugin $plugin */
    foreach (self::$pluginsToRegister as $plugin) {
      $pluginLowerName = strtolower($plugin->getPluginName());
      $underscoreName = GeneralUtility::camelCaseToLowerCaseUnderscored($plugin->getExtensionKey());
      ExtensionUtility::registerPlugin(
        $plugin->getExtensionKey(),
        $plugin->getPluginName(),
        'LLL:EXT:' . $underscoreName . '/Resources/Private/Language/locallang_be.xlf:content_element.' . $pluginLowerName,
        self::getIconFilePath($plugin)
      );

      if ($plugin->getFlexFormFileName()) {
        $pluginSignature = self::getExtensionShortName($plugin) . '_' . self::getPluginId($plugin);
        $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';

        ExtensionManagementUtility::addPiFlexFormValue(
          $pluginSignature,
          'FILE:EXT:' . $underscoreName . '/Configuration/FlexForms/' . $plugin->getFlexFormFileName()
        );
      }
    }
    self::$pluginsToRegister = [];
  }

  public static function definePlugin(Plugin $plugin): void
  {
    self::$pluginsToConfigure[] = $plugin;
    self::$pluginsToRegister[] = $plugin;
  }

  private static function getPluginId(Plugin $plugin): string
  {
    return strtolower($plugin->getPluginName());
  }

  private static function getIconFilePath(Plugin $plugin): string
  {
    $fileName = $plugin->getIconFileName();
    $underscoreName = GeneralUtility::camelCaseToLowerCaseUnderscored($plugin->getExtensionKey());
    $result = 'EXT:' . $underscoreName . '/Resources/Public/Icons/' . $fileName;

    return $result;
  }

  private static function getExtensionShortName(Plugin $plugin): string
  {
    $extensionName = preg_replace('/[\s,_]+/', '', $plugin->getExtensionKey());

    return strtolower($extensionName);
  }

  private static function getPluginSignature(Plugin $plugin): string
  {
    return self::getExtensionShortName($plugin) . '_' . self::getPluginId($plugin);
  }
}
