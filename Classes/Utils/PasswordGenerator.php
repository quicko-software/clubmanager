<?php

namespace Quicko\Clubmanager\Utils;

use TYPO3\CMS\Core\Core\Environment;

class PasswordGenerator
{
  private static function getRandomChar(string $charSet) : string {
    $n = rand(0, strlen($charSet) - 1);
    $char = $charSet[$n];
    return $char;
  }

  public static function generatePassword(): string
  {
    $lower = 'abcdefghijklmnopqrstuvwxyz';
    $upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $digits = '1234567890';
    $specials = '.;:-_#!?$';
    
    $passwordTemplateChars = [];
    $len = 12;
    $loopCount = $len / 4;
    for ($i = 0; $i < $loopCount; ++$i) {
      $passwordTemplateChars[]= self::getRandomChar($lower);
      $passwordTemplateChars[]= self::getRandomChar($upper);
      $passwordTemplateChars[]= self::getRandomChar($digits);
      $passwordTemplateChars[]= self::getRandomChar($specials);
    }
    shuffle($passwordTemplateChars);
    return implode($passwordTemplateChars);
  }
}
