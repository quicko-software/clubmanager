<?php

namespace Quicko\Clubmanager\Domain\Model;

use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

class Plugin
{
  /**
   * Summary of __construct.
   *
   * @param array<string,string> $controllerActions
   * @param array<string,string> $nonCacheableControllerActions
   */
  public function __construct(string $extensionKey, string $pluginName, array $controllerActions, array $nonCacheableControllerActions, string $iconFileName, string $wizardGroupId = null, string $flexFormFileName = '', string $pluginType = ExtensionUtility::PLUGIN_TYPE_PLUGIN)
  {
    $this->extensionKey = $extensionKey;
    $this->pluginName = $pluginName;
    $this->controllerActions = $controllerActions;
    $this->nonCacheableControllerActions = $nonCacheableControllerActions;
    $this->iconFileName = $iconFileName;
    $this->wizardGroupId = $wizardGroupId;
    $this->flexFormFileName = $flexFormFileName;
    $this->pluginType = $pluginType;
  }
  protected string $extensionKey;

  protected string $pluginName;

  /**
   * @var array<string,string>
   */
  protected $controllerActions;

  /**
   * @var array<string,string>
   */
  protected $nonCacheableControllerActions;

  protected string $pluginType;

  protected string $iconFileName;

  protected string $wizardGroupId;

  protected string $flexFormFileName;

  public function getPluginType(): string
  {
    return $this->pluginType;
  }

  /**
   * @return array<string,string>
   */
  public function getNonCacheableControllerActions(): array
  {
    return $this->nonCacheableControllerActions;
  }

  /**
   * @return array<string,string>
   */
  public function getControllerActions(): array
  {
    return $this->controllerActions;
  }

  public function getPluginName(): string
  {
    return $this->pluginName;
  }

  public function getExtensionKey(): string
  {
    return $this->extensionKey;
  }

  public function getIconFileName(): string
  {
    return $this->iconFileName;
  }

  public function getFlexFormFileName(): string
  {
    return $this->flexFormFileName;
  }

  public function getWizardGroupId(): string
  {
    return $this->wizardGroupId;
  }
}
