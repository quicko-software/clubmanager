<?php

namespace Quicko\Clubmanager\Utils;

use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class BankAccountDataHelper
{
  // https://stackoverflow.com/questions/20983339/validate-iban-php
  public static function isValidIBAN(string $iban): bool
  {

    $iban = strtolower($iban);
    $countries = array(
      'al' => 28, 'ad' => 24, 'at' => 20, 'az' => 28, 'bh' => 22, 'be' => 16, 'ba' => 20, 'br' => 29, 'bg' => 22, 'cr' => 21, 'hr' => 21, 'cy' => 28, 'cz' => 24,
      'dk' => 18, 'do' => 28, 'ee' => 20, 'fo' => 18, 'fi' => 18, 'fr' => 27, 'ge' => 22, 'de' => 22, 'gi' => 23, 'gr' => 27, 'gl' => 18, 'gt' => 28, 'hu' => 28,
      'is' => 26, 'ie' => 22, 'il' => 23, 'it' => 27, 'jo' => 30, 'kz' => 20, 'kw' => 30, 'lv' => 21, 'lb' => 28, 'li' => 21, 'lt' => 20, 'lu' => 20, 'mk' => 19,
      'mt' => 31, 'mr' => 27, 'mu' => 30, 'mc' => 27, 'md' => 24, 'me' => 22, 'nl' => 18, 'no' => 15, 'pk' => 24, 'ps' => 29, 'pl' => 28, 'pt' => 25, 'qa' => 29,
      'ro' => 24, 'sm' => 27, 'sa' => 24, 'rs' => 22, 'sk' => 24, 'si' => 19, 'es' => 24, 'se' => 24, 'ch' => 21, 'tn' => 24, 'tr' => 26, 'ae' => 23, 'gb' => 22, 'vg' => 24
    );
    $chars = array(
      'a' => 10, 'b' => 11, 'c' => 12, 'd' => 13, 'e' => 14, 'f' => 15, 'g' => 16, 'h' => 17, 'i' => 18, 'j' => 19, 'k' => 20, 'l' => 21, 'm' => 22,
      'n' => 23, 'o' => 24, 'p' => 25, 'q' => 26, 'r' => 27, 's' => 28, 't' => 29, 'u' => 30, 'v' => 31, 'w' => 32, 'x' => 33, 'y' => 34, 'z' => 35
    );

    if (strlen($iban) != $countries[substr($iban, 0, 2)]) {
      return false;
    }

    $movedChar = substr($iban, 4) . substr($iban, 0, 4);
    $movedCharArray = str_split($movedChar);
    $newString = "";

    foreach ($movedCharArray as $k => $v) {

      if (!is_numeric($movedCharArray[$k])) {
        $movedCharArray[$k] = $chars[$movedCharArray[$k]];
      }
      $newString .= $movedCharArray[$k];
    }
    if (function_exists("bcmod")) {
      return bcmod($newString, '97') == 1;
    }

    // http://au2.php.net/manual/en/function.bcmod.php#38474
    $x = $newString;
    $y = "97";
    $take = 5;
    $mod = "";

    do {
      $a = (int)$mod . substr($x, 0, $take);
      $x = substr($x, $take);
      $mod = $a % $y;
    } while (strlen($x));

    return (int)$mod == 1;
  }

  public static function isValidBIC(string $bic): bool
  {
    return preg_match('/^[a-z]{6}[0-9a-z]{2}([0-9a-z]{3})?\z/i',  $bic);
  }

  public static function sanitizeIban(string $iban, string $ident = ""): string
  {
    $result = preg_replace('/[^A-Za-z0-9]/', '', $iban);
    if (!BankAccountDataHelper::isValidIBAN($result)) {
      $errorText = LocalizationUtility::translate("sepa.error.invalid_user_iban", "Clubmanager", [$iban]);
      if (!empty($ident)) {
        $errorText .= " ($ident)";
      }
      throw new \InvalidArgumentException($errorText);
    }
    return $result;

  }
  public static function sanitizeBIC(string $bic, string $ident = ""): string
  {
    $result = preg_replace('/[^A-Za-z0-9]/', '', $bic);
    if (!BankAccountDataHelper::isValidBIC($result)) {
      $errorText = LocalizationUtility::translate("sepa.error.invalid_user_bic", "Clubmanager", [$bic]);
      if (!empty($ident)) {
        $errorText .= " ($ident)";
      }
      throw new \InvalidArgumentException($errorText);
    }
    return $result;
  }
}
