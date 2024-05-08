<?php

namespace Quicko\Clubmanager\Records;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class MemberRecordRepository extends BaseRecordRepository implements SingletonInterface
{
  protected string $table = 'tx_clubmanager_domain_model_member';

  protected function createSelect(): QueryBuilder
  {
    $queryBuilder = $this->getQueryBuilder();
    $queryBuilder
      ->select(
        'member.uid',
        'member.pid',
        'member.ident',
        'member.state',
        'member.firstname',
        'member.lastname',
        'member.company',
        'member.city',
        'member.email',
        'member.level',
        'countries.cn_short_local AS country',
        'fe_users.email AS fe_users_email',
        'fe_users.uid AS fe_users_uid',
        'fe_users.pid AS fe_users_pid',
      )
      ->from($this->table, 'member')
      ->leftJoin(
        'member',
        'static_countries',
        'countries',
        $queryBuilder->expr()->eq('countries.uid', $queryBuilder->quoteIdentifier('member.country'))
      )
      ->leftJoin(
        'member',
        'fe_users',
        'fe_users',
        $queryBuilder->expr()->and(
          $queryBuilder->expr()->eq('fe_users.clubmanager_member', $queryBuilder->quoteIdentifier('member.uid')),
          $queryBuilder->expr()->eq('fe_users.deleted', 0),
        )
      );

    return $queryBuilder;
  }

  public function findByUid($uid)
  {
    $queryBuilder = $this->createSelect();
    $queryBuilder
      ->andWhere($queryBuilder->expr()->eq('member.deleted', 0))
      ->andWhere($queryBuilder->expr()->eq('member.uid', $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT)));

    return $queryBuilder
      ->execute()
      ->fetchAssociative();
  }


  public function findRecursively($pid)
  {
    $pids = $this->getTreePids($pid);

    $queryBuilder = $this->createSelect();
    $queryBuilder->andWhere($queryBuilder->expr()->eq('member.deleted', 0))
      ->andWhere($queryBuilder->expr()->in('member.pid', $queryBuilder->createNamedParameter($pids, Connection::PARAM_INT_ARRAY)))
      ->orderBy('member.ident', 'DESC');

    return $queryBuilder
      ->execute()
      ->fetchAllAssociative();
  }

  protected function getTreePids($rootPid): array
  {
    $depth = 999999;
    $queryGenerator = GeneralUtility::makeInstance(\Quicko\Clubmanager\Domain\Helper\QueryGenerator::class);
    $childPids = $queryGenerator->getTreeList($rootPid, $depth, 0, 1); // Will be a string like 1,2,3

    return $childPids;
  }
}
