<?php

namespace Quicko\Clubmanager\Configuration;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Crypto\Random;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\FrontendLogin\Configuration\RecoveryConfiguration;

class ExtRecoveryConfiguration extends RecoveryConfiguration
{
  public function __construct(
    protected Context $context,
    ConfigurationManagerInterface $configurationManager,
    Random $random,
  ) {
    $hashService = null;
    if (GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() < 13) {
      $hashService = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Security\Cryptography\HashService::class);
    } else {
      $hashService = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Crypto\HashService::class);
    }
    // parent::__construct($context, $configurationManager, $random, $hashService);
    // $this->settings = $configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS);
    //
    // Don't call $configurationManager->getConfiguration(...) here because it requires
    // a http request which is not present in cli-context (scheduler).
    // As far as I can see now the settings are not neccessary (2025-10-23,stephanw).
    // 
    $this->fixSettings();
    $this->forgotHash = $this->getLifeTimeTimestamp() . '|' . $this->generateHash($random, $hashService);
    $this->resolveFromTypoScript();
  }

  protected function fixSettings(): void
  {
    /** TYPO 12 */
    $this->settings['email_from']= $this->settings['email_from'] ?? '';
    $this->settings['email_fromName']= $this->settings['email_fromName'] ?? '';


    $this->mailTemplateName = (string) ($this->settings['email']['templateName'] ?? '');
    if (empty($this->mailTemplateName)) {
      $this->settings['email']['templateName'] = 'PasswordRecovery';
    }
    /** @var ExtensionConfiguration $config */
    $config = GeneralUtility::makeInstance(ExtensionConfiguration::class);
    $this->settings['forgotLinkHashValidTime'] = intval($config->get('clubmanager', 'passwordRecoveryLifeTime'));
  }
}
