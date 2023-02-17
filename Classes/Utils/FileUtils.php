<?php

namespace Quicko\Clubmanager\Utils;

use TYPO3\CMS\Core\Core\Environment;

class FileUtils
{
  public static function generateTempFilename($prefix = "temp",$ext = ".tmp"): string
  {
    $varPath = Environment::getVarPath();
    $filename = "";
    while (true) {
      $filename = $varPath . "/" . uniqid($prefix, true) . $ext;
      if (!file_exists($filename)) break;
    }
    return $filename;
  }
}
