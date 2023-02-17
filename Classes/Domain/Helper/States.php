<?php

namespace Quicko\Clubmanager\Domain\Helper;


class States
{

  public static function getStates() : array
  {
    return [
      ['Außerhalb Deutschlands', '0'],
      ['Niedersachsen', '79'],
      ['Baden Wuerttemberg', '80'],
      ['Bayern', '81'],
      ['Berlin', '82'],
      ['Brandenburg', '83'],
      ['Bremen', '84'],
      ['Hamburg', '85'],
      ['Hessen', '86'],
      ['Mecklenburg Vorpommern', '87'],
      ['Nordrhein Westfalen', '88'],
      ['Rheinland Pfalz', '89'],
      ['Saarland', '90'],
      ['Sachsen', '91'],
      ['Sachsen Anhalt', '92'],
      ['Schleswig Holstein', '93'],
      ['Thueringen', '94'],
    ];
  }

  public static function getStatesObjects() : array
  {
    $result = [];
    $statesArray = self::getStates();
    
    foreach ($statesArray as $value) {
      $object = new \stdClass();
      $object->key = $value[1];
      $object->value = $value[0];
      $result[] = $object;
    }
    return $result;
  }
}
