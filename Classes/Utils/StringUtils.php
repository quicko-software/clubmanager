<?php

namespace Quicko\Clubmanager\Utils;

class StringUtils
{

  public static function startsWith($haystack, $needle)
  {
    $length = strlen($needle);
    return substr($haystack, 0, $length) === $needle;
  }

  public static function sanitizer4filename($file)
  {
    $file = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $file);
    $file = mb_ereg_replace("([\.]{2,})", '', $file);
    return $file;
  }
}
