<?php

namespace Quicko\Clubmanager\Records;


class CachedMemberRecordRepository extends MemberRecordRepository
{

  protected $memberCache;

  public function __construct() {
    $this->fillCache();
  }
  
  public function findAll()
  {
    $queryBuilder = $this->createSelect();
    $queryBuilder
      ->andWhere($queryBuilder->expr()->eq('member.deleted', 0));

    return $queryBuilder
      ->execute()
      ->fetchAllAssociative();
  }

  public function findByUid($uid)
  {
    if(!array_key_exists($uid, $this->memberCache)) return null;
    return $this->memberCache[$uid];
  }

  public function findRecursively($pid)
  {
    $pids = $this->getTreePids($pid);

    $results = [];  
    foreach($this->memberCache as $key => $value) {
      if (in_array($value['pid'], $pids )) {
        $results[] = $value;
      }
    }
    usort($results, fn($a, $b) => $a['ident'] < $b['ident']);
    return $results;
  }  

  public function fillCache()
  {
    if (!$this->memberCache) {
      $array = $this->findAll();
      $this->memberCache = [];
      foreach($array as $key => $value) {
        $this->memberCache[$value['uid']] = $value;
      }
    }
  }
}
