<?php

namespace Quicko\Clubmanager\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\Repository;

class FrontendUserRepository extends Repository
{
  public function findAllIncludingDisabled() {
    $query = $this->createQuery();
    $querySettings = $query->getQuerySettings();
    $querySettings->setIgnoreEnableFields(true); // also hidden, but not deleted
    $querySettings->setRespectStoragePage(false); // everywhere (potentially)
    return $query->execute();
  }
}