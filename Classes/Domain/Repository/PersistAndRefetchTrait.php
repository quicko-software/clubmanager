<?php

namespace Quicko\Clubmanager\Domain\Repository;

/**
 * To be used as a 'mixin' in Extbase Repository-classes.
 * Adds member functions to persist domain objects immediately.
 *
 */
trait PersistAndRefetchTrait
{
  public function persistAll(): void
  {
    $this->persistenceManager->persistAll();
  }

  public function clearState(): void
  {
    $this->persistenceManager->clearState();
  }

  /**
   * @param \TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject $domainObject
   *
   * TODO: types
   * @phpstan-ignore-next-line
   */
  public function persistAndRefetch($domainObject)
  {
    if (is_null($domainObject->getUid())) {
      /** @phpstan-ignore-next-line */
      $this->add($domainObject);
    } else {
      /** @phpstan-ignore-next-line */
      $this->update($domainObject);
    }
    $this->persistAll();
    $this->clearState();
    $domainObject = $this->findByUid($domainObject->getUid());

    return $domainObject;
  }
}
