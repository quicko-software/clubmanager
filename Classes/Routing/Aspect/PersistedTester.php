<?php
namespace Quicko\Clubmanager\Routing\Aspect;

use TYPO3\CMS\Core\Routing\Aspect\PersistedAliasMapper;

class PersistedTester extends PersistedAliasMapper {

  /**
   * {@inheritdoc}
   */
  public function resolve(string $value): ?string {
   // debug($value);
    if(parent::resolve($value)) {
      return $value;
    }
    return null;
  } 

  /**
   * {@inheritdoc}
   */
  public function generate(string $value): ?string {
    //debug($value);
    if(parent::generate($value)) {
      return $value;
    }
    return null;
  }

}
