<?php

namespace Quicko\Clubmanager\Routing\Aspect;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SanitizeValueUidMapper extends SanitizeValue
{
  /**
   * map uid to value.
   */
  protected function getTableValue(string $value): ?string
  {
    /** @var ConnectionPool $connectionPool */
    $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
    $queryBuilder = $connectionPool->getQueryBuilderForTable($this->tableName);
    $queryResult = $queryBuilder
      ->select($this->columnName)
      ->from($this->tableName)
      ->where(
        $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($value)),
      )
      ->execute()
      ->fetchOne()
    ;
    if (false === $queryResult) {
      return null;
    }

    return $queryResult;
  }

  /**
   * map value to uid.
   */
  protected function getTableUid(string $value): ?string
  {
    $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($this->tableName);
    $queryResult = $queryBuilder
      ->select('uid')
      ->from($this->tableName)
      ->where(
        $queryBuilder->expr()->eq($this->columnName, $queryBuilder->createNamedParameter($value)),
      )
      ->execute()
      ->fetchOne()
    ;
    if (false === $queryResult) {
      return null;
    }

    return $queryResult;
  }

  public function resolve(string $sanitizedValue): ?string
  {
    $value = SanitizeValue::resolve($sanitizedValue);
    if (!$value) {
      return null;
    }

    return $this->getTableUid($value);
  }

  public function generate(string $originalValue): ?string
  {
    $originalValue = $this->getTableValue($originalValue);
    if (!$originalValue) {
      return null;
    }

    return SanitizeValue::generate($originalValue);
  }
}
