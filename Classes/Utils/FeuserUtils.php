<?php

namespace Quicko\Clubmanager\Utils;

use \TYPO3\CMS\Core\Context\Context;
use \TYPO3\CMS\Core\Utility\GeneralUtility;

class FeuserUtils
{
  public static function getCurrentFeusername() {
    $context = GeneralUtility::makeInstance(Context::class);
    $username = $context->getPropertyFromAspect('frontend.user', 'username');
    return $username;
  }
}
