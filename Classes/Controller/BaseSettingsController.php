<?php

namespace Quicko\Clubmanager\Controller;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class BaseSettingsController extends ActionController
{
    protected function setDefaultSettingsIfRequired() {
        $this->updateDefaultSettingsIfRequired();  
        $this->view->assign('settings', $this->settings);        
    }

    protected function updateDefaultSettingsIfRequired() {
        $config = GeneralUtility::makeInstance(ExtensionConfiguration::class);
        if(empty($this->settings["detailCityPage"])) {
            $this->settings["detailCityPage"] = $config->get('clubmanager', 'defaultDetailCityPage');
        }
        if(empty($this->settings["detailMemberPage"])) {
            $this->settings["detailMemberPage"] = $config->get('clubmanager', 'defaultDetailMemberPage');
        }        
        if(empty($this->settings["detailLocationPage"])) {
            $this->settings["detailLocationPage"] = $config->get('clubmanager', 'defaultDetailLocationPage');
        }      
    }
}
