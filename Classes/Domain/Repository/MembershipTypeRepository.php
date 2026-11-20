<?php

declare(strict_types=1);

namespace Quicko\Clubmanager\Domain\Repository;

use Quicko\Clubmanager\Domain\Model\MembershipType;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Stammdaten liegen in beliebigen Ordnern; die Auswahl begrenzt bei Bedarf
 * `MembershipMasterDataService` über das Site-Setting `clubmanager.membershipMasterDataPids`.
 *
 * @extends Repository<MembershipType>
 */
class MembershipTypeRepository extends Repository
{
  public function initializeObject(): void
  {
    $querySettings = $this->createQuery()->getQuerySettings();
    if ($querySettings instanceof Typo3QuerySettings) {
      $querySettings->setRespectStoragePage(false);
    }
    $this->setDefaultQuerySettings($querySettings);
  }
}
