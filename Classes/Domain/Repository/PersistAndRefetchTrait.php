<?php

namespace Quicko\Clubmanager\Domain\Repository;

/**
 * To be used as a 'mixin' in Extbase Repository-classes.
 * Adds member functions to persist domain objects immediately.
 *
 * @template T of \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface
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
   * @param object $domainObject
   *
   * @phpstan-param T $domainObject
   *
   * @return object|null The matching object if found, otherwise NULL
   *
   * @phpstan-return T|null
   */
  public function persistAndRefetch($domainObject)
  {
    if (is_null($domainObject->getUid())) {
      $this->add($domainObject);
    } else {
      $this->update($domainObject);
    }
    $this->persistAll();
    $this->clearState();
    $domainObject = $this->findByUid($domainObject->getUid());

    return $domainObject;
  }
}
