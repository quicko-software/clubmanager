<?php

namespace Quicko\Clubmanager\Controller;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class BaseSettingsController extends ActionController
{
  protected function setDefaultSettingsIfRequired(): void
  {
    $this->updateDefaultSettingsIfRequired();
    $this->view->assign('settings', $this->settings);
  }

  protected function updateDefaultSettingsIfRequired(): void
  {
    /** @var ExtensionConfiguration $config */
    $config = GeneralUtility::makeInstance(ExtensionConfiguration::class);
    if (empty($this->settings['detailCityPage'])) {
      $this->settings['detailCityPage'] = $config->get('clubmanager', 'defaultDetailCityPage');
    }
    if (empty($this->settings['detailMemberPage'])) {
      $this->settings['detailMemberPage'] = $config->get('clubmanager', 'defaultDetailMemberPage');
    }
    if (empty($this->settings['detailLocationPage'])) {
      $this->settings['detailLocationPage'] = $config->get('clubmanager', 'defaultDetailLocationPage');
    }
    if (empty($this->settings['uidCategoryMember'])) {
      $this->settings['uidCategoryMember'] = $config->get('clubmanager', 'uidCategoryMember');
    }
    if (empty($this->settings['uidCategoryLocation'])) {
      $this->settings['uidCategoryLocation'] = $config->get('clubmanager', 'uidCategoryLocation');
    }
  }
}
