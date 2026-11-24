<?php

declare(strict_types=1);

namespace Quicko\Clubmanager\Service;

use Psr\EventDispatcher\EventDispatcherInterface;
use Quicko\Clubmanager\Event\MembershipMasterDataUsageEvent;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Localization\LanguageService;

/**
 * Ermittelt, wo Mitgliedschaftstypen und Level verwendet werden (#6425, E10).
 *
 * Mitglieder und Journal-Einträge (auch verborgene, nur gelöschte nicht) zählt der Kern
 * selbst; Verwendungen anderer Extensions kommen über `MembershipMasterDataUsageEvent`.
 */
final class MembershipMasterDataUsageService
{
  private const MEMBER_TABLE = 'tx_clubmanager_domain_model_member';
  private const JOURNAL_TABLE = 'tx_clubmanager_domain_model_memberjournalentry';

  /** @var array<string, array{member: string, journal: list<string>}> */
  private const REFERENCE_FIELDS = [
    MembershipMasterDataService::TABLE_TYPE => [
      'member' => 'membership_type',
      'journal' => ['old_membership_type', 'new_membership_type'],
    ],
    MembershipMasterDataService::TABLE_LEVEL => [
      'member' => 'member_level',
      'journal' => ['old_member_level', 'new_member_level'],
    ],
  ];

  public function __construct(
    private readonly ConnectionPool $connectionPool,
    private readonly EventDispatcherInterface $eventDispatcher,
  ) {}

  public static function isMasterDataTable(string $table): bool
  {
    return isset(self::REFERENCE_FIELDS[$table]);
  }

  /**
   * @return array<string, int> lesbare Bezeichnung => Anzahl; leer heißt unbenutzt
   */
  public function usages(string $table, int $uid): array
  {
    if (!self::isMasterDataTable($table) || $uid <= 0) {
      return [];
    }
    $fields = self::REFERENCE_FIELDS[$table];
    $event = new MembershipMasterDataUsageEvent($table, $uid);
    $event->addUsage(
      self::label('masterdata.usage.members', 'Members'),
      $this->countReferences(self::MEMBER_TABLE, [$fields['member']], $uid)
    );
    $event->addUsage(
      self::label('masterdata.usage.journal', 'Journal entries'),
      $this->countReferences(self::JOURNAL_TABLE, $fields['journal'], $uid)
    );

    $this->eventDispatcher->dispatch($event);

    return $event->getUsages();
  }

  /**
   * Verwendete Typen und Level auf einer Seite und allen Unterseiten (Seite löschen).
   *
   * @return list<array{table: string, uid: int, title: string, usages: array<string, int>}>
   */
  public function usedRecordsInPageTree(int $pageUid): array
  {
    if ($pageUid <= 0) {
      return [];
    }
    $pageUids = $this->pageTreeUids($pageUid);
    $used = [];
    foreach (array_keys(self::REFERENCE_FIELDS) as $table) {
      $queryBuilder = $this->queryBuilder($table);
      $rows = $queryBuilder
        ->select('uid', 'title')
        ->from($table)
        ->where(
          $queryBuilder->expr()->in('pid', $queryBuilder->createNamedParameter($pageUids, Connection::PARAM_INT_ARRAY)),
          $queryBuilder->expr()->in('sys_language_uid', [0, -1])
        )
        ->executeQuery()
        ->fetchAllAssociative();
      foreach ($rows as $row) {
        $usages = $this->usages($table, (int)$row['uid']);
        if ($usages !== []) {
          $used[] = ['table' => $table, 'uid' => (int)$row['uid'], 'title' => (string)$row['title'], 'usages' => $usages];
        }
      }
    }

    return $used;
  }

  /**
   * @param list<string> $fields
   */
  private function countReferences(string $table, array $fields, int $uid): int
  {
    $queryBuilder = $this->queryBuilder($table);
    $constraints = array_map(
      static fn(string $field) => $queryBuilder->expr()->eq($field, $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT)),
      $fields
    );

    return (int)$queryBuilder
      ->count('uid')
      ->from($table)
      ->where($queryBuilder->expr()->or(...$constraints))
      ->executeQuery()
      ->fetchOne();
  }

  /**
   * @return list<int>
   */
  private function pageTreeUids(int $pageUid): array
  {
    $all = [$pageUid];
    $current = [$pageUid];
    while ($current !== []) {
      $queryBuilder = $this->queryBuilder('pages');
      $current = array_map('intval', $queryBuilder
        ->select('uid')
        ->from('pages')
        ->where($queryBuilder->expr()->in('pid', $queryBuilder->createNamedParameter($current, Connection::PARAM_INT_ARRAY)))
        ->executeQuery()
        ->fetchFirstColumn());
      $current = array_values(array_diff($current, $all));
      $all = [...$all, ...$current];
    }

    return $all;
  }

  private function queryBuilder(string $table): QueryBuilder
  {
    $queryBuilder = $this->connectionPool->getQueryBuilderForTable($table);
    $queryBuilder->getRestrictions()->removeAll()->add(new DeletedRestriction());

    return $queryBuilder;
  }

  private static function label(string $key, string $fallback): string
  {
    $languageService = $GLOBALS['LANG'] ?? null;
    if (!$languageService instanceof LanguageService) {
      return $fallback;
    }

    return $languageService->sL('LLL:EXT:clubmanager/Resources/Private/Language/locallang_be.xlf:' . $key) ?: $fallback;
  }
}
