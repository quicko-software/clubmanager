<?php

namespace Quicko\Clubmanager\Domain\Repository;

///
/// To be used as a 'mixin' in Extbase Repository-classes.
/// Adds member functions to persist domain objects immediately.
///
trait PersistAndRefetchTrait {

  public function persistAll() {
    $this->persistenceManager->persistAll();
  }

  public function clearState() {
    $this->persistenceManager->clearState();
  }
  
  public function persistAndRefetch($domainObject) {
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
