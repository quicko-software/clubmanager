<?php

namespace Quicko\Clubmanager\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;

use Quicko\Clubmanager\Domain\Repository\PersistAndRefetchTrait;


class SocialmediaRepository extends Repository
{
  use PersistAndRefetchTrait;

  public function initializeObject()
  {
    $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
    $querySettings->setRespectStoragePage(false);
    $this->setDefaultQuerySettings($querySettings);
  }

}
