<?php

namespace Quicko\Clubmanager\Records;


class CachedMemberRecordRepository extends MemberRecordRepository
{

  /**
   * @var array<array<string,mixed>>
   */
  protected $memberCache;

  public function __construct() {
    $this->fillCache();
  }
  
  public function findAll(): array
  {
    $queryBuilder = $this->createSelect();
    $queryBuilder
      ->andWhere($queryBuilder->expr()->eq('member.deleted', 0));

    return $queryBuilder
      ->executeQuery()
      ->fetchAllAssociative();
  }

  /**
   * @return array<string,mixed>|false
   */
  public function findByUid(int $uid)
  {
    if(!array_key_exists($uid, $this->memberCache)) return false;
    return $this->memberCache[$uid];
  }

  /**
   * @return array<array<string,mixed>>
   */
  public function findRecursively(int $pid): array
  {
    $pids = $this->getTreePids($pid);

    $results = [];  
    foreach($this->memberCache as $key => $value) {
      if (in_array($value['pid'], $pids )) {
        $results[] = $value;
      }
    }
    
    usort($results, static fn($a, $b): int => $a['ident'] < $b['ident'] ? -1 : 1);
    return $results;
  }  

  public function fillCache(): void
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
