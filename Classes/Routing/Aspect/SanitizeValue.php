<?php

namespace Quicko\Clubmanager\Routing\Aspect;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Routing\Aspect\MappableAspectInterface;
use TYPO3\CMS\Core\Routing\Aspect\StaticMappableAspectInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

use Quicko\Clubmanager\Utils\SlugUtil;


class SanitizeValue implements MappableAspectInterface, StaticMappableAspectInterface
{

  /**
   * @var string
   */
  protected $tableName;

  /**
   * @var string
   */
  protected $columnName;

  /**
   * @param array $settings
   * @throws \InvalidArgumentException
   */
  public function __construct(array $settings)
  {
    $this->tableName = $settings['tableName'] ?? "";
    $this->columnName = $settings['columnName'] ?? "";
  }

  /** 
   * Get the configured QueryBuilder
  */
  protected function getQueryBuilder()
  {
    $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_clubmanager_sanitizevalue_mapping');
    $queryBuilder->getRestrictions()
      ->removeAll();
    return $queryBuilder;
  }

  /**
   * Exists a mapping already?
   */
  protected function isSanitizedValueMappingExists(string $sanitizedValue,?string $originalValue = null): bool
  {
    $queryBuilder = $this->getQueryBuilder();
    $queryBuilder
      ->count('uid')
      ->from('tx_clubmanager_sanitizevalue_mapping')
      ->where(
        $queryBuilder->expr()->eq('sanitized_value', $queryBuilder->createNamedParameter($sanitizedValue)),
        $queryBuilder->expr()->eq('table_name', $queryBuilder->createNamedParameter($this->tableName)),
        $queryBuilder->expr()->eq('column_name', $queryBuilder->createNamedParameter($this->columnName))
      );
    if($originalValue != null) {
      $queryBuilder->andWhere(
        $queryBuilder->expr()->eq('original_value', $queryBuilder->createNamedParameter($originalValue))
      );
    }

    $count = $queryBuilder->execute()->fetchOne();
    return $count === false ? false : $count > 0;
  }

  /**
   * Existing the original value in the source table?
   */
  protected function isValueIsValidInOrginalTable(string $value): bool
  {
    $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($this->tableName);
    $queryResult = $queryBuilder
      ->count($this->columnName)
      ->from($this->tableName)
      ->where(
        $queryBuilder->expr()->eq($this->columnName, $queryBuilder->createNamedParameter($value)),
      )
      ->execute()
    ;

    $count = $queryBuilder->execute()->fetchOne();
    return $count === false ? false : $count > 0;
  }

  /**
   * save the mapping
   */
  protected function storeSanitiedValueMapping(string $originalValue, string $sanitizedValue)
  {
    $queryBuilder = $this->getQueryBuilder();
    $queryBuilder
      ->insert('tx_clubmanager_sanitizevalue_mapping')
      ->values([
        'original_value' => $originalValue,
        'sanitized_value' => $sanitizedValue,
        'table_name' => $this->tableName,
        'column_name' => $this->columnName
      ])
      ->execute();
  }

  /**
   * make the sanitizedValue unique
   */
  protected function makeSanitizedValueUnique($sanitizedValue) {
    $ct = 1;
    $originalSanitizedValue = $sanitizedValue;
    while($this->isSanitizedValueMappingExists($sanitizedValue)) {
      $sanitizedValue = $originalSanitizedValue . "-" . $ct++;
    }
    return $sanitizedValue;
  }

  /**
   * search the orginial value in the db
   */
  protected function getMappedValue(string $value): ?string
  {
    $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($this->tableName);
    $queryResult = $queryBuilder
      ->select('original_value')
      ->from('tx_clubmanager_sanitizevalue_mapping')
      ->where(
        $queryBuilder->expr()->eq('sanitized_value', $queryBuilder->createNamedParameter($value)),
        $queryBuilder->expr()->eq('table_name', $queryBuilder->createNamedParameter($this->tableName)),
        $queryBuilder->expr()->eq('column_name', $queryBuilder->createNamedParameter($this->columnName))
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
   * {@inheritdoc}
   */
  public function resolve(string $sanitizedValue): ?string
  {
    $originalValue = $this->getMappedValue($sanitizedValue);
    if(!$originalValue) $originalValue = $sanitizedValue;
    return $this->isValueIsValidInOrginalTable($originalValue) ? $originalValue : null;
  }

  /**
   * {@inheritdoc}
   */
  public function generate(string $originalValue): ?string
  {
    $sanitizedValue = SlugUtil::sanitizeParameter($originalValue);
    if ($sanitizedValue != $originalValue) {
      if (!$this->isSanitizedValueMappingExists($sanitizedValue,$originalValue)
      && $this->isValueIsValidInOrginalTable($originalValue)) {
        $sanitizedValue = $this->makeSanitizedValueUnique($sanitizedValue);
        $this->storeSanitiedValueMapping($originalValue, $sanitizedValue);
      }
    } else if(!$this->isValueIsValidInOrginalTable($originalValue)) {
        return null;
    }
    return $sanitizedValue;
  }
}
