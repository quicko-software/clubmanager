<?php

namespace Quicko\Clubmanager\Configuration;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Crypto\HashService;
use TYPO3\CMS\Core\Crypto\Random;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\FrontendLogin\Configuration\RecoveryConfiguration;

class ExtRecoveryConfiguration extends RecoveryConfiguration
{
  /**
   * parent::__construct() nicht: getConfiguration() braucht einen HTTP-Request (CLI/Scheduler hat keinen).
   *
   * @phpstan-ignore constructor.unusedParameter
   */
  public function __construct(
    protected Context $context,
    ConfigurationManagerInterface $configurationManager,
    Random $random,
    HashService $hashService,
  ) {
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
