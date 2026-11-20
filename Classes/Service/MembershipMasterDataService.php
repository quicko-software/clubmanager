<?php

declare(strict_types=1);

namespace Quicko\Clubmanager\Service;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\SiteFinder;

/**
 * Liefert die für neue Zuordnungen wählbaren Mitgliedschaftstypen und Level.
 *
 * Typen und Level dürfen in beliebigen Ordnern liegen. Ist im Site-Setting
 * `clubmanager.membershipMasterDataPids` eine Liste von Seiten-UIDs hinterlegt,
 * kommen nur Datensätze dieser Seiten in Frage; leer heißt ohne Einschränkung.
 * Deaktivierte Datensätze (`inactive`) und Übersetzungen werden nie angeboten.
 */
final class MembershipMasterDataService
{
  public const SETTING_PIDS = 'clubmanager.membershipMasterDataPids';
  public const TABLE_TYPE = 'tx_clubmanager_domain_model_membershiptype';
  public const TABLE_LEVEL = 'tx_clubmanager_domain_model_memberlevel';
  public const TABLE_MM = 'tx_clubmanager_memberlevel_membershiptype_mm';

  public function __construct(
    private readonly ConnectionPool $connectionPool,
    private readonly SiteFinder $siteFinder,
  ) {}

  /**
   * Seiten, auf die die Auswahl begrenzt ist; leer heißt ohne Einschränkung.
   *
   * @param int $contextPid Seite des bearbeiteten Datensatzes (bestimmt die Site)
   * @return list<int>
   */
  public function restrictionPids(int $contextPid): array
  {
    if ($contextPid <= 0) {
      return [];
    }
    try {
      $value = $this->siteFinder->getSiteByPageId($contextPid)->getSettings()->get(self::SETTING_PIDS, '');
    } catch (SiteNotFoundException) {
      return [];
    }

    return self::parsePidList($value);
  }

  /**
   * @return list<array{uid: int, pid: int, title: string, person_type: int}>
   */
  public function selectableTypes(int $contextPid): array
  {
    $queryBuilder = $this->queryBuilder(self::TABLE_TYPE);
    $queryBuilder
      ->select('t.uid', 't.pid', 't.title', 't.person_type')
      ->from(self::TABLE_TYPE, 't')
      ->where(...$this->selectableConstraints($queryBuilder, 't', $contextPid))
      ->orderBy('t.sorting')
      ->addOrderBy('t.uid');

    return array_map(
      static fn(array $row): array => [
        'uid' => (int)$row['uid'],
        'pid' => (int)$row['pid'],
        'title' => (string)$row['title'],
        'person_type' => (int)$row['person_type'],
      ],
      $queryBuilder->executeQuery()->fetchAllAssociative()
    );
  }

  /**
   * Level, die dem Typ zugeordnet und wählbar sind, aufsteigend nach Rang.
   *
   * @return list<array{uid: int, pid: int, title: string, ranking: int, display_class: string}>
   */
  public function selectableLevels(int $membershipTypeUid, int $contextPid): array
  {
    if ($membershipTypeUid <= 0) {
      return [];
    }
    $queryBuilder = $this->queryBuilder(self::TABLE_LEVEL);
    $queryBuilder
      ->select('l.uid', 'l.pid', 'l.title', 'l.ranking', 'l.display_class')
      ->from(self::TABLE_LEVEL, 'l')
      ->join('l', self::TABLE_MM, 'mm', $queryBuilder->expr()->eq('mm.uid_local', $queryBuilder->quoteIdentifier('l.uid')))
      ->where(
        $queryBuilder->expr()->eq('mm.uid_foreign', $queryBuilder->createNamedParameter($membershipTypeUid, Connection::PARAM_INT)),
        ...$this->selectableConstraints($queryBuilder, 'l', $contextPid)
      )
      ->orderBy('l.ranking')
      ->addOrderBy('l.sorting')
      ->addOrderBy('l.uid');

    return array_map(
      static fn(array $row): array => [
        'uid' => (int)$row['uid'],
        'pid' => (int)$row['pid'],
        'title' => (string)$row['title'],
        'ranking' => (int)$row['ranking'],
        'display_class' => (string)$row['display_class'],
      ],
      $queryBuilder->executeQuery()->fetchAllAssociative()
    );
  }

  public function isSelectableCombination(int $membershipTypeUid, int $memberLevelUid, int $contextPid): bool
  {
    foreach ($this->selectableLevels($membershipTypeUid, $contextPid) as $level) {
      if ($level['uid'] === $memberLevelUid) {
        return true;
      }
    }

    return false;
  }

  /**
   * Akzeptiert "12, 34", Listen und einzelne Zahlen; ungültige Einträge entfallen.
   *
   * @return list<int>
   */
  public static function parsePidList(mixed $value): array
  {
    if (is_int($value)) {
      $value = [$value];
    }
    if (is_string($value)) {
      $value = explode(',', $value);
    }
    if (!is_array($value)) {
      return [];
    }
    $pids = [];
    foreach ($value as $item) {
      if (is_int($item) || (is_string($item) && ctype_digit(trim($item)))) {
        $pid = (int)$item;
        if ($pid > 0) {
          $pids[$pid] = $pid;
        }
      }
    }

    return array_values($pids);
  }

  /**
   * @return list<string>
   */
  private function selectableConstraints(QueryBuilder $queryBuilder, string $alias, int $contextPid): array
  {
    $constraints = [
      $queryBuilder->expr()->eq($alias . '.inactive', 0),
      $queryBuilder->expr()->in($alias . '.sys_language_uid', [0, -1]),
    ];
    $pids = $this->restrictionPids($contextPid);
    if ($pids !== []) {
      $constraints[] = $queryBuilder->expr()->in(
        $alias . '.pid',
        $queryBuilder->createNamedParameter($pids, Connection::PARAM_INT_ARRAY)
      );
    }

    return $constraints;
  }

  private function queryBuilder(string $table): QueryBuilder
  {
    $queryBuilder = $this->connectionPool->getQueryBuilderForTable($table);
    $queryBuilder->getRestrictions()->removeAll()->add(new DeletedRestriction());

    return $queryBuilder;
  }
}
