<?php

declare(strict_types=1);

namespace Quicko\Clubmanager\Service;

use Quicko\Clubmanager\Domain\Model\Member;
use Quicko\Clubmanager\Domain\Repository\MemberLevelRepository;
use Quicko\Clubmanager\Domain\Repository\MembershipTypeRepository;
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
  public const SETTING_DEFAULT_TYPE = 'clubmanager.defaultMembershipType';
  public const SETTING_DEFAULT_LEVEL = 'clubmanager.defaultMemberLevel';
  public const TABLE_TYPE = 'tx_clubmanager_domain_model_membershiptype';
  public const TABLE_LEVEL = 'tx_clubmanager_domain_model_memberlevel';
  public const TABLE_MM = 'tx_clubmanager_memberlevel_membershiptype_mm';

  public function __construct(
    private readonly ConnectionPool $connectionPool,
    private readonly SiteFinder $siteFinder,
    private readonly MembershipTypeRepository $membershipTypeRepository,
    private readonly MemberLevelRepository $memberLevelRepository,
  ) {}

  /**
   * Seiten, auf die die Auswahl begrenzt ist; leer heißt ohne Einschränkung.
   *
   * @param int $contextPid Seite des bearbeiteten Datensatzes (bestimmt die Site)
   * @return list<int>
   */
  public function restrictionPids(int $contextPid): array
  {
    return self::parsePidList($this->siteSetting($contextPid, self::SETTING_PIDS, ''));
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

  /**
   * Alle wählbaren Level unabhängig vom Typ, aufsteigend nach Rang (z. B. Beitragssätze „für alle Typen“).
   *
   * @return list<array{uid: int, pid: int, title: string, ranking: int, display_class: string}>
   */
  public function selectableLevelsOfAnyType(int $contextPid): array
  {
    $queryBuilder = $this->queryBuilder(self::TABLE_LEVEL);
    $queryBuilder
      ->select('l.uid', 'l.pid', 'l.title', 'l.ranking', 'l.display_class')
      ->from(self::TABLE_LEVEL, 'l')
      ->where(...$this->selectableConstraints($queryBuilder, 'l', $contextPid))
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
   * Werte, die ein verarbeiteter Level-Wechsel am Mitglied setzt.
   *
   * Mit Verweisen: `membership_type`, `person_type` (aus dem Typ), `member_level` und
   * `level` = Rang des Levels. Das alte Feld `level` wird im Übergang mitgeschrieben, bis
   * Pro, Billing und Statistik den Level-Datensatz lesen; der Upgrade-Wizard legt die Level
   * mit Rang 0/10/20/30 an, damit die Werte zusammenpassen. Ohne Verweise (Alteinträge)
   * gilt nur `new_level`. Deaktivierte Datensätze werden hier bewusst nicht gefiltert.
   *
   * @return array<string, int>
   */
  public function memberFieldsForLevelChange(int $newMembershipTypeUid, int $newMemberLevelUid, ?int $legacyNewLevel): array
  {
    $values = [];
    $level = $newMemberLevelUid > 0 ? $this->fetchRecord(self::TABLE_LEVEL, $newMemberLevelUid, ['ranking']) : null;
    if ($level !== null) {
      $values['member_level'] = $newMemberLevelUid;
      $values['level'] = (int)$level['ranking'];
    }
    $type = $newMembershipTypeUid > 0 ? $this->fetchRecord(self::TABLE_TYPE, $newMembershipTypeUid, ['person_type']) : null;
    if ($type !== null) {
      $values['membership_type'] = $newMembershipTypeUid;
      $values['person_type'] = (int)$type['person_type'];
    }
    if (!isset($values['level']) && $legacyNewLevel !== null) {
      $values['level'] = $legacyNewLevel;
    }

    return $values;
  }

  /**
   * Startwerte für ein neues Mitglied aus den Site-Settings `clubmanager.defaultMembershipType`
   * und `clubmanager.defaultMemberLevel` (gleiche Felder wie `memberFieldsForLevelChange()`).
   *
   * Der Typ muss wählbar sein, sonst gibt es keine Startwerte. Ist das Level nicht gesetzt oder
   * passt es nicht zum Typ, gilt das wählbare Level des Typs mit dem niedrigsten Rang.
   *
   * @return array<string, int> leer, wenn kein gültiger Starttyp konfiguriert ist
   */
  public function defaultsForNewMember(int $contextPid): array
  {
    $typeUid = (int)$this->siteSetting($contextPid, self::SETTING_DEFAULT_TYPE, 0);
    if ($typeUid <= 0 || !in_array($typeUid, array_column($this->selectableTypes($contextPid), 'uid'), true)) {
      return [];
    }
    $levels = $this->selectableLevels($typeUid, $contextPid);
    $levelUid = (int)$this->siteSetting($contextPid, self::SETTING_DEFAULT_LEVEL, 0);
    if (!in_array($levelUid, array_column($levels, 'uid'), true)) {
      $levelUid = (int)($levels[0]['uid'] ?? 0);
    }

    return $this->memberFieldsForLevelChange($typeUid, $levelUid, null);
  }

  /**
   * Setzt die Startwerte an einem neuen Mitglied (Extbase, z. B. Registrierung), wenn es noch
   * keinen Typ hat. Die Site ergibt sich aus der Seite des Mitglieds.
   */
  public function applyNewMemberDefaults(Member $member): void
  {
    if ($member->getMembershipType() !== null) {
      return;
    }
    $defaults = $this->defaultsForNewMember((int)$member->getPid());
    if ($defaults === []) {
      return;
    }
    $member->setMembershipType($this->membershipTypeRepository->findByUid($defaults['membership_type']));
    $member->setPersonType($defaults['person_type']);
    if (isset($defaults['member_level'])) {
      $member->setMemberLevel($this->memberLevelRepository->findByUid($defaults['member_level']));
      $member->setLevel($defaults['level']);
    }
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

  /**
   * @param list<string> $fields
   * @return array<string, mixed>|null
   */
  private function fetchRecord(string $table, int $uid, array $fields): ?array
  {
    $queryBuilder = $this->queryBuilder($table);
    $row = $queryBuilder
      ->select(...$fields)
      ->from($table)
      ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT)))
      ->setMaxResults(1)
      ->executeQuery()
      ->fetchAssociative();

    return is_array($row) ? $row : null;
  }

  private function siteSetting(int $contextPid, string $key, mixed $default): mixed
  {
    if ($contextPid <= 0) {
      return $default;
    }
    try {
      return $this->siteFinder->getSiteByPageId($contextPid)->getSettings()->get($key, $default);
    } catch (SiteNotFoundException) {
      return $default;
    }
  }

  private function queryBuilder(string $table): QueryBuilder
  {
    $queryBuilder = $this->connectionPool->getQueryBuilderForTable($table);
    $queryBuilder->getRestrictions()->removeAll()->add(new DeletedRestriction());

    return $queryBuilder;
  }
}
