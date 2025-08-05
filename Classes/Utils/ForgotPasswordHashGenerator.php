<?php

namespace Quicko\Clubmanager\Utils;

use TYPO3\CMS\FrontendLogin\Configuration\RecoveryConfiguration;

class ForgotPasswordHashGenerator
{
  public function __construct(protected RecoveryConfiguration $recoveryConfiguration)
  {
  }

  public function generate(): string
  {
    return $this->recoveryConfiguration->getForgotHash();
  }
}
