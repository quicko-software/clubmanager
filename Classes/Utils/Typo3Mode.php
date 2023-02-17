<?php

namespace Quicko\Clubmanager\Utils;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\ApplicationType;

///
/// Replacement for the deprecated TYPO3_MODE constant.
///
class Typo3Mode {
  ///
  /// Returns true in the frontend context.
  /// Returns false in the backend context or cli context.
  ///
  public static function isFrontend() {
    // https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/11.0/Deprecation-92947-DeprecateTYPO3_MODEAndTYPO3_REQUESTTYPEConstants.html
    $request = $GLOBALS['TYPO3_REQUEST'] ?? null;
    if ($request instanceof ServerRequestInterface) {
      return ApplicationType::fromRequest($request)->isFrontend();
    }
    return false;
  }
}
