<?php

namespace Quicko\Clubmanager\Records;

use TYPO3\CMS\Core\SingletonInterface;

use Quicko\Clubmanager\Records\BaseRecordRepository;

class FeUserRecordRepository extends BaseRecordRepository implements SingletonInterface
{

  /**
   * @var string
   */
  protected string $table = 'fe_users';
  
}
