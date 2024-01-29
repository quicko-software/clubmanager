<?php

namespace Quicko\Clubmanager\Domain\Repository;

use Quicko\Clubmanager\Domain\Model\Socialmedia;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * @extends Repository<Socialmedia>
 */
class SocialmediaRepository extends Repository
{
  use PersistAndRefetchTrait;

  public function initializeObject(): void
  {
    /** @var Typo3QuerySettings $querySettings */
    $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
    $querySettings->setRespectStoragePage(false);
    $this->setDefaultQuerySettings($querySettings);
  }
}
