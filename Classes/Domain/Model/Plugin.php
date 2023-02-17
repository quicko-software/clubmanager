<?php

namespace Quicko\Clubmanager\Domain\Model;

use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

class Plugin
{


  public function __construct($extensionKey, $pluginName, $controllerActions, $nonCacheableControllerActions, $iconFileName, $wizardGroupId = null, $flexFormFileName = '', $pluginType = ExtensionUtility::PLUGIN_TYPE_PLUGIN)
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

  /**
   * @var string
   */
  protected $extensionKey;

  /**
   * @var string
   */
  protected $pluginName;

  /**
   * @var array
   */
  protected $controllerActions;

  /**
   * @var array
   */
  protected $nonCacheableControllerActions;

  /**
   * @var string
   */
  protected $pluginType;

  /**
   * @var string
   */
  protected $iconFileName;

  /**
   * @var string
   */
  protected $wizardGroupId;  

  /**
   * @var string
   */
  protected $flexFormFileName;


  /**
   * Get the value of pluginType
   *
   * @return  string
   */
  public function getPluginType()
  {
    return $this->pluginType;
  }

  /**
   * Get the value of nonCacheableControllerActions
   *
   * @return  array
   */
  public function getNonCacheableControllerActions()
  {
    return $this->nonCacheableControllerActions;
  }

  /**
   * Get the value of controllerActions
   *
   * @return  array
   */
  public function getControllerActions()
  {
    return $this->controllerActions;
  }


  /**
   * Get the value of pluginName
   *
   * @return  string
   */
  public function getPluginName()
  {
    return $this->pluginName;
  }

  /**
   * Get the value of extensionKey
   *
   * @return  string
   */
  public function getExtensionKey()
  {
    return $this->extensionKey;
  }
  /**
   * Get the value of iconFileName
   *
   * @return  string
   */
  public function getIconFileName()
  {
    return $this->iconFileName;
  }

  /**
   * Get the value of flexFormFileName
   *
   * @return  string
   */
  public function getFlexFormFileName()
  {
    return $this->flexFormFileName;
  }

  /**
   * Get the value of wizardGroupId
   *
   * @return  string
   */ 
  public function getWizardGroupId()
  {
    return $this->wizardGroupId;
  }

}
