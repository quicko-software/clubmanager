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

  /**
   * @var array<string, true>
   */
  protected static array $registeredSignatures = [];

  /**
   * @var array<string, string>
   */
  protected static array $iconsToRegister = [];

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
      self::collectIcon($plugin);
    }
    self::$pluginsToConfigure = [];
  }

  public static function registerCollectedIcons(IconRegistry $iconRegistry): void
  {
    foreach (self::$iconsToRegister as $identifier => $source) {
      if ($iconRegistry->isRegistered($identifier)) {
        continue;
      }
      $iconRegistry->registerIcon(
        $identifier,
        SvgIconProvider::class,
        ['source' => $source]
      );
    }
    self::$iconsToRegister = [];
  }

  public static function registerAllPlugins(): void
  {
    if (!isset($GLOBALS['TCA']['tt_content']['columns']['CType']['config']['itemGroups']['clubmanager'])) {
      $GLOBALS['TCA']['tt_content']['columns']['CType']['config']['itemGroups']['clubmanager'] = 'Clubmanager';
    }

    /** @var Plugin $plugin */
    foreach (self::$pluginsToRegister as $plugin) {
      $pluginSignature = self::getPluginSignature($plugin);
      if (isset(self::$registeredSignatures[$pluginSignature])) {
        continue;
      }
      self::$registeredSignatures[$pluginSignature] = true;

      $underscoreName = GeneralUtility::camelCaseToLowerCaseUnderscored($plugin->getExtensionKey());
      $pluginLowerName = strtolower($plugin->getPluginName());
      $wizardGroup = $plugin->getWizardGroupId() !== '' ? $plugin->getWizardGroupId() : 'plugins';

      ExtensionUtility::registerPlugin(
        $plugin->getExtensionKey(),
        $plugin->getPluginName(),
        'LLL:EXT:' . $underscoreName . '/Resources/Private/Language/locallang_be.xlf:content_element.' . $pluginLowerName,
        self::getIconIdentifier($plugin),
        $wizardGroup,
        'LLL:EXT:' . $underscoreName . '/Resources/Private/Language/locallang_be.xlf:content_element.' . $pluginLowerName . '.description'
      );

      if ($plugin->getFlexFormFileName()) {
        ExtensionManagementUtility::addPiFlexFormValue(
          '*',
          'FILE:EXT:' . $underscoreName . '/Configuration/FlexForms/' . $plugin->getFlexFormFileName(),
          $pluginSignature
        );

        $showitem = (string)($GLOBALS['TCA']['tt_content']['types'][$pluginSignature]['showitem'] ?? '');
        if ($showitem !== '' && !str_contains($showitem, 'pi_flexform')) {
          ExtensionManagementUtility::addToAllTCAtypes(
            'tt_content',
            '--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.plugin,pi_flexform',
            $pluginSignature,
            'after:header'
          );
        }
      }
    }
    self::$pluginsToRegister = [];
  }

  public static function definePlugin(Plugin $plugin): void
  {
    self::$pluginsToConfigure[] = $plugin;
    self::$pluginsToRegister[] = $plugin;
  }

  private static function collectIcon(Plugin $plugin): void
  {
    self::$iconsToRegister[self::getIconIdentifier($plugin)] = self::getIconFilePath($plugin);
  }

  private static function getIconIdentifier(Plugin $plugin): string
  {
    $underscoreName = GeneralUtility::camelCaseToLowerCaseUnderscored($plugin->getExtensionKey());

    return 'ext-' . $underscoreName . '-content-' . self::getPluginId($plugin) . '-icon';
  }

  private static function getPluginId(Plugin $plugin): string
  {
    return strtolower($plugin->getPluginName());
  }

  private static function getIconFilePath(Plugin $plugin): string
  {
    $fileName = $plugin->getIconFileName();
    $underscoreName = GeneralUtility::camelCaseToLowerCaseUnderscored($plugin->getExtensionKey());

    return 'EXT:' . $underscoreName . '/Resources/Public/Icons/' . $fileName;
  }

  private static function getExtensionShortName(Plugin $plugin): string
  {
    $extensionName = preg_replace('/[\s,_]+/', '', $plugin->getExtensionKey());

    return strtolower($extensionName ?? '');
  }

  private static function getPluginSignature(Plugin $plugin): string
  {
    return self::getExtensionShortName($plugin) . '_' . self::getPluginId($plugin);
  }
}
