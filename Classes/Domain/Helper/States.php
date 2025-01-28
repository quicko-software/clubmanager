<?php

namespace Quicko\Clubmanager\Domain\Helper;

use stdClass;

class States
{
  public static function getStates(): array
  {
    return [
      ['label' => 'Außerhalb Deutschlands', 'value' => '0'],
      ['label' => 'Niedersachsen', 'value' => '79'],
      ['label' => 'Baden Wuerttemberg', 'value' => '80'],
      ['label' => 'Bayern', 'value' => '81'],
      ['label' => 'Berlin', 'value' => '82'],
      ['label' => 'Brandenburg', 'value' => '83'],
      ['label' => 'Bremen', 'value' => '84'],
      ['label' => 'Hamburg', 'value' => '85'],
      ['label' => 'Hessen', 'value' => '86'],
      ['label' => 'Mecklenburg Vorpommern', 'value' => '87'],
      ['label' => 'Nordrhein Westfalen', 'value' => '88'],
      ['label' => 'Rheinland Pfalz', 'value' => '89'],
      ['label' => 'Saarland', 'value' => '90'],
      ['label' => 'Sachsen', 'value' => '91'],
      ['label' => 'Sachsen Anhalt', 'value' => '92'],
      ['label' => 'Schleswig Holstein', 'value' => '93'],
      ['label' => 'Thueringen', 'value' => '94'],
    ];
  }

}
