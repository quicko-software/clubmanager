<?php

namespace Quicko\Clubmanager\Configuration;

use TYPO3\CMS\FrontendLogin\Configuration\RecoveryConfiguration;

class ExtRecoveryConfiguration extends RecoveryConfiguration
{
  protected function resolveFromTypoScript(): void
  {
    $this->mailTemplateName = (string) ($this->settings['email']['templateName'] ?? '');
    if (empty($this->mailTemplateName)) {
      $this->settings['email']['templateName'] = 'DUMMY';
    }

    parent::resolveFromTypoScript();
  }
}
