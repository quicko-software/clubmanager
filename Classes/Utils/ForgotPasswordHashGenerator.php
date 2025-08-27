<?php

namespace Quicko\Clubmanager\Utils;

use Quicko\Clubmanager\Configuration\ExtRecoveryConfiguration;

class ForgotPasswordHashGenerator
{
  public function __construct(
    protected ExtRecoveryConfiguration $recoveryConfiguration
  ) {
  }

  public function generate(): string
  {
    return $this->recoveryConfiguration->getForgotHash();
  }

  public function getLifeTimeTimestamp(): int
  {
    return $this->recoveryConfiguration->getLifeTimeTimestamp();
  }
}
