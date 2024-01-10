<?php

namespace Quicko\Clubmanager\Utils;

class StringUtils
{
  public static function startsWith(string $haystack, string $needle): bool
  {
    $length = strlen($needle);

    return substr($haystack, 0, $length) === $needle;
  }

  public static function sanitizer4filename(string $file): string
  {
    $file = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $file);
    if ($file) {
      $file = mb_ereg_replace("([\.]{2,})", '', $file);
    }

    return !empty($file) ? $file : '';
  }
}
