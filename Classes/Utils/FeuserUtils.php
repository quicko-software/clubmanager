<?php

namespace Quicko\Clubmanager\Utils;

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FeuserUtils
{
  public static function getCurrentFeusername(): ?string
  {
    /** @var Context $context */
    $context = GeneralUtility::makeInstance(Context::class);
    /** @var ?string $username */
    $username = $context->getPropertyFromAspect('frontend.user', 'username');

    return $username;
  }
}
