<?php

declare(strict_types=1);

namespace Quicko\Clubmanager\Updates;

use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\AbstractListTypeToCTypeUpdate;

#[UpgradeWizard('clubmanager_listTypeToCType')]
final class ListTypeToCTypeUpdate extends AbstractListTypeToCTypeUpdate
{
  protected function getListTypeToCTypeMapping(): array
  {
    return [
      'clubmanager_citieslist' => 'clubmanager_citieslist',
      'clubmanager_city' => 'clubmanager_city',
      'clubmanager_member' => 'clubmanager_member',
      'clubmanager_memberlist' => 'clubmanager_memberlist',
      'clubmanager_location' => 'clubmanager_location',
      'clubmanager_locationlist' => 'clubmanager_locationlist',
    ];
  }

  public function getTitle(): string
  {
    return 'Clubmanager: Migrate list_type plugins to CType';
  }

  public function getDescription(): string
  {
    return 'Migrates Clubmanager frontend plugins from tt_content.list_type to dedicated CType values.';
  }
}
