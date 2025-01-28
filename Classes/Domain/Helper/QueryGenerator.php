<?php

namespace Quicko\Clubmanager\Domain\Helper;

use PDO;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryHelper;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class QueryGenerator
{
  /**
   * Recursively fetch all descendants of a given page.
   *
   * @param int $id uid of the page
   *
   * @return array array of descendant pages
   */
  public function getTreeList(int $id, int $depth, int $begin = 0, string $permClause = ''): array
  {
    if ($id < 0) {
      $id = abs($id);
    }
    $theList = [];
    if ($begin === 0) {
      $theList[] = $id;
    }
    if ($id && $depth > 0) {
      $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
      $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
      $queryBuilder->select('uid')
        ->from('pages')
        ->where(
          $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($id, PDO::PARAM_INT)),
          $queryBuilder->expr()->eq('sys_language_uid', 0)
        )
        ->orderBy('uid');
      if ($permClause !== '') {
        $queryBuilder->andWhere(QueryHelper::stripLogicalOperatorPrefix($permClause));
      }
      $statement = $queryBuilder->executeQuery();
      while ($row = $statement->fetchAssociative()) {
        if ($begin <= 0) {
          $theList[] = $row['uid'];
        }
        if ($depth > 1) {
          $theSubList = $this->getTreeList($row['uid'], $depth - 1, $begin - 1, $permClause);
          $theList = array_merge($theList, $theSubList);
        }
      }
    }

    return $theList;
  }
}
