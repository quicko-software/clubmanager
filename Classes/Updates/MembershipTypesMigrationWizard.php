<?php

declare(strict_types=1);

namespace Quicko\Clubmanager\Updates;

use Quicko\Clubmanager\Domain\Model\Member;
use Quicko\Clubmanager\Domain\Model\MemberJournalEntry;
use Quicko\Clubmanager\Service\MembershipMasterDataService;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\ChattyInterface;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

/**
 * Überführt Personenart und feste Level-Stufen in Mitgliedschaftstyp- und Level-Datensätze (#6425).
 *
 * - Typen: je Personenart einer („Person“ = natürlich, „Einrichtung“ = juristisch).
 * - Level: je Stufe einer mit Rang = alter Wert (0/10/20/30 → Basis, Bronze, Silber, Gold) und
 *   der bisherigen Farbklasse; jedes Level ist beiden Typen zugeordnet. Unbekannte Werte
 *   bekommen ein eigenes Level mit diesem Rang.
 * - Mitglieder (auch gelöschte) ohne Typ bekommen Typ und Level; Level-Wechsel im Journal ohne
 *   Verweise bekommen alten und neuen Typ (aus der Personenart des Mitglieds) und Level.
 * - Sites mit Clubmanager-Set bekommen die Startwerte für neue Mitglieder (Typ „Person“,
 *   Level mit Rang 0), sofern dort noch keine gesetzt sind.
 *
 * Vorhandene Datensätze werden wiederverwendet (Typ über Personenart, Level über Rang), ein
 * zweiter Lauf ändert nichts. Die alten Felder `level`/`person_type` bleiben unverändert.
 */
#[UpgradeWizard('clubmanager_membershipTypesMigration')]
final class MembershipTypesMigrationWizard implements UpgradeWizardInterface, ChattyInterface
{
  private const TABLE_MEMBER = 'tx_clubmanager_domain_model_member';
  private const TABLE_JOURNAL = 'tx_clubmanager_domain_model_memberjournalentry';

  /** @var array<int, string> */
  private const TYPE_TITLES = [
    Member::PERSON_TYPE_NATURAL => 'Person',
    Member::PERSON_TYPE_JURIDICAL => 'Einrichtung',
  ];

  /** @var array<int, array{title: string, display_class: string}> */
  private const LEVELS = [
    Member::LEVEL_BASE => ['title' => 'Basis', 'display_class' => 'default'],
    Member::LEVEL_BRONZE => ['title' => 'Bronze', 'display_class' => 'secondary'],
    Member::LEVEL_SILVER => ['title' => 'Silber', 'display_class' => 'tertiary'],
    Member::LEVEL_GOLD => ['title' => 'Gold', 'display_class' => 'quaternary'],
  ];

  private OutputInterface $output;

  /** @var array<int, int> Personenart => Typ-UID */
  private array $typeUids = [];

  /** @var array<int, int> Rang => Level-UID */
  private array $levelUids = [];

  public function __construct(
    private readonly ConnectionPool $connectionPool,
    private readonly SiteFinder $siteFinder,
  ) {
    $this->output = new NullOutput();
  }

  public function setOutput(OutputInterface $output): void
  {
    $this->output = $output;
  }

  public function getTitle(): string
  {
    return 'Clubmanager: Mitgliedschaftstypen und Level als Datensätze';
  }

  public function getDescription(): string
  {
    return 'Legt aus Personenart und den festen Stufen 0/10/20/30 Mitgliedschaftstypen und Level an, '
      . 'verknüpft Mitglieder und Level-Wechsel im Journal damit und setzt die Startwerte für neue '
      . 'Mitglieder in den Site-Settings. Wiederholbar; vorhandene Typen und Level werden wiederverwendet.';
  }

  /**
   * @return string[]
   */
  public function getPrerequisites(): array
  {
    return [DatabaseUpdatedPrerequisite::class];
  }

  public function updateNecessary(): bool
  {
    return $this->countMembersWithoutType() > 0
      || $this->countJournalEntriesWithoutLevel() > 0
      || $this->sitesWithoutDefaults() !== [];
  }

  public function executeUpdate(): bool
  {
    $storagePid = $this->masterDataStoragePid();
    if ($storagePid <= 0) {
      $this->output->writeln('Keine Mitglieder und kein Mitgliederordner gefunden, nichts zu tun.');
      return true;
    }

    $this->ensureMasterData($storagePid, $this->usedRankings());
    $members = $this->migrateMembers();
    $entries = $this->migrateJournal();
    $sites = $this->writeSiteDefaults();

    $this->output->writeln(sprintf(
      'Fertig: %d Mitglieder, %d Journal-Einträge, %d Site(s) mit Startwerten. Typen und Level liegen auf Seite %d.',
      $members,
      $entries,
      $sites,
      $storagePid
    ));

    return true;
  }

  /**
   * Ordner mit den meisten Mitgliedern; ohne Mitglieder der Mitgliederordner der ersten Site.
   */
  private function masterDataStoragePid(): int
  {
    $queryBuilder = $this->queryBuilder(self::TABLE_MEMBER);
    $pid = $queryBuilder
      ->select('pid')
      ->addSelectLiteral('COUNT(*) AS cnt')
      ->from(self::TABLE_MEMBER)
      ->where($queryBuilder->expr()->gt('pid', 0))
      ->groupBy('pid')
      ->orderBy('cnt', 'DESC')
      ->addOrderBy('pid')
      ->setMaxResults(1)
      ->executeQuery()
      ->fetchOne();
    if ($pid !== false && (int)$pid > 0) {
      return (int)$pid;
    }
    foreach ($this->clubmanagerSites() as $site) {
      $storagePid = (int)$site->getSettings()->get('clubmanager.storagePid', 0);
      if ($storagePid > 0) {
        return $storagePid;
      }
    }

    return 0;
  }

  /**
   * @param list<int> $rankings
   */
  private function ensureMasterData(int $storagePid, array $rankings): void
  {
    foreach (array_keys(self::TYPE_TITLES) as $personType) {
      $this->typeUids[$personType] = $this->findTypeUid($personType)
        ?? $this->insertRecord(MembershipMasterDataService::TABLE_TYPE, $storagePid, [
          'title' => self::TYPE_TITLES[$personType],
          'person_type' => $personType,
          'sorting' => 256 * ($personType + 1),
        ]);
    }

    $rankings = array_values(array_unique([...array_keys(self::LEVELS), ...$rankings]));
    sort($rankings);
    foreach ($rankings as $index => $ranking) {
      $levelUid = $this->findLevelUid($ranking);
      if ($levelUid === null) {
        $levelUid = $this->insertRecord(MembershipMasterDataService::TABLE_LEVEL, $storagePid, [
          'title' => self::LEVELS[$ranking]['title'] ?? ('Level ' . $ranking),
          'ranking' => $ranking,
          'display_class' => self::LEVELS[$ranking]['display_class'] ?? '',
          'sorting' => 256 * ($index + 1),
        ]);
        foreach (array_values($this->typeUids) as $typeIndex => $typeUid) {
          $this->connectionPool->getConnectionForTable(MembershipMasterDataService::TABLE_MM)->insert(
            MembershipMasterDataService::TABLE_MM,
            ['uid_local' => $levelUid, 'uid_foreign' => $typeUid, 'sorting' => $typeIndex + 1, 'sorting_foreign' => $index + 1]
          );
        }
        $this->output->writeln(sprintf('Level „%s“ (Rang %d) angelegt.', self::LEVELS[$ranking]['title'] ?? ('Level ' . $ranking), $ranking));
      }
      $this->levelUids[$ranking] = $levelUid;
    }
    $this->updateRelationCounters();
  }

  private function migrateMembers(): int
  {
    $queryBuilder = $this->queryBuilder(self::TABLE_MEMBER);
    $rows = $queryBuilder
      ->select('uid', 'person_type', 'level')
      ->from(self::TABLE_MEMBER)
      ->where($queryBuilder->expr()->eq('membership_type', 0))
      ->executeQuery()
      ->fetchAllAssociative();
    $connection = $this->connectionPool->getConnectionForTable(self::TABLE_MEMBER);
    foreach ($rows as $row) {
      $connection->update(self::TABLE_MEMBER, [
        'membership_type' => $this->typeFor((int)$row['person_type']),
        'member_level' => $this->levelUids[(int)$row['level']],
      ], ['uid' => (int)$row['uid']]);
    }

    return count($rows);
  }

  private function migrateJournal(): int
  {
    $queryBuilder = $this->queryBuilder(self::TABLE_JOURNAL);
    $rows = $queryBuilder
      ->select('j.uid', 'j.old_level', 'j.new_level', 'm.person_type')
      ->from(self::TABLE_JOURNAL, 'j')
      ->leftJoin('j', self::TABLE_MEMBER, 'm', $queryBuilder->expr()->eq('m.uid', $queryBuilder->quoteIdentifier('j.member')))
      ->where(...$this->journalWithoutLevelConstraints($queryBuilder, 'j'))
      ->executeQuery()
      ->fetchAllAssociative();
    $connection = $this->connectionPool->getConnectionForTable(self::TABLE_JOURNAL);
    foreach ($rows as $row) {
      $typeUid = $this->typeFor((int)($row['person_type'] ?? 0));
      $connection->update(self::TABLE_JOURNAL, [
        'old_membership_type' => $typeUid,
        'new_membership_type' => $typeUid,
        'old_member_level' => $this->levelUids[(int)$row['old_level']] ?? 0,
        'new_member_level' => $this->levelUids[(int)$row['new_level']] ?? 0,
      ], ['uid' => (int)$row['uid']]);
    }

    return count($rows);
  }

  private function writeSiteDefaults(): int
  {
    $written = 0;
    foreach ($this->sitesWithoutDefaults() as $site) {
      $file = Environment::getConfigPath() . '/sites/' . $site->getIdentifier() . '/settings.yaml';
      $content = is_file($file) ? (string)file_get_contents($file) : '';
      $content = rtrim($content) . ($content === '' ? '' : "\n\n")
        . "# #6425: Mitgliedschaftstyp und Level für neue Mitglieder (Upgrade-Wizard clubmanager_membershipTypesMigration)\n"
        . MembershipMasterDataService::SETTING_DEFAULT_TYPE . ': ' . $this->typeUids[Member::PERSON_TYPE_NATURAL] . "\n"
        . MembershipMasterDataService::SETTING_DEFAULT_LEVEL . ': ' . $this->levelUids[Member::LEVEL_BASE] . "\n";
      GeneralUtility::writeFile($file, $content, true);
      $this->output->writeln(sprintf('Startwerte in sites/%s/settings.yaml geschrieben.', $site->getIdentifier()));
      $written++;
    }

    return $written;
  }

  private function typeFor(int $personType): int
  {
    return $this->typeUids[$personType] ?? $this->typeUids[Member::PERSON_TYPE_NATURAL];
  }

  /**
   * @return list<int> alle Stufen in Mitgliedern und Journal-Einträgen, die noch migriert werden
   */
  private function usedRankings(): array
  {
    $rankings = [];
    $queryBuilder = $this->queryBuilder(self::TABLE_MEMBER);
    foreach ($queryBuilder->select('level')->from(self::TABLE_MEMBER)->groupBy('level')->executeQuery()->fetchFirstColumn() as $level) {
      $rankings[] = (int)$level;
    }
    $queryBuilder = $this->queryBuilder(self::TABLE_JOURNAL);
    $rows = $queryBuilder
      ->select('old_level', 'new_level')
      ->from(self::TABLE_JOURNAL)
      ->where(...$this->journalWithoutLevelConstraints($queryBuilder, self::TABLE_JOURNAL))
      ->groupBy('old_level', 'new_level')
      ->executeQuery()
      ->fetchAllAssociative();
    foreach ($rows as $row) {
      $rankings[] = (int)$row['old_level'];
      $rankings[] = (int)$row['new_level'];
    }

    return array_values(array_unique($rankings));
  }

  private function findTypeUid(int $personType): ?int
  {
    $queryBuilder = $this->queryBuilder(MembershipMasterDataService::TABLE_TYPE);
    $uid = $queryBuilder
      ->select('uid')
      ->from(MembershipMasterDataService::TABLE_TYPE)
      ->where(
        $queryBuilder->expr()->eq('deleted', 0),
        $queryBuilder->expr()->in('sys_language_uid', [0, -1]),
        $queryBuilder->expr()->eq('person_type', $queryBuilder->createNamedParameter($personType, Connection::PARAM_INT))
      )
      ->orderBy('inactive')
      ->addOrderBy('sorting')
      ->addOrderBy('uid')
      ->setMaxResults(1)
      ->executeQuery()
      ->fetchOne();

    return $uid === false ? null : (int)$uid;
  }

  private function findLevelUid(int $ranking): ?int
  {
    $queryBuilder = $this->queryBuilder(MembershipMasterDataService::TABLE_LEVEL);
    $uid = $queryBuilder
      ->select('uid')
      ->from(MembershipMasterDataService::TABLE_LEVEL)
      ->where(
        $queryBuilder->expr()->eq('deleted', 0),
        $queryBuilder->expr()->in('sys_language_uid', [0, -1]),
        $queryBuilder->expr()->eq('ranking', $queryBuilder->createNamedParameter($ranking, Connection::PARAM_INT))
      )
      ->orderBy('inactive')
      ->addOrderBy('sorting')
      ->addOrderBy('uid')
      ->setMaxResults(1)
      ->executeQuery()
      ->fetchOne();

    return $uid === false ? null : (int)$uid;
  }

  /**
   * @param array<string, int|string> $fields
   */
  private function insertRecord(string $table, int $pid, array $fields): int
  {
    $connection = $this->connectionPool->getConnectionForTable($table);
    $connection->insert($table, [...$fields, 'pid' => $pid, 'tstamp' => time(), 'crdate' => time()]);
    $uid = (int)$connection->lastInsertId();
    if ($table === MembershipMasterDataService::TABLE_TYPE) {
      $this->output->writeln(sprintf('Mitgliedschaftstyp „%s“ angelegt.', (string)$fields['title']));
    }

    return $uid;
  }

  /**
   * Zählerfelder der n:m-Beziehung (Level: Zahl der Typen, Typ: Zahl der Level) nachziehen.
   */
  private function updateRelationCounters(): void
  {
    $mm = MembershipMasterDataService::TABLE_MM;
    foreach ([[MembershipMasterDataService::TABLE_LEVEL, 'membership_types', 'uid_local'], [MembershipMasterDataService::TABLE_TYPE, 'member_levels', 'uid_foreign']] as [$table, $field, $mmField]) {
      $uids = $table === MembershipMasterDataService::TABLE_LEVEL ? $this->levelUids : $this->typeUids;
      foreach ($uids as $uid) {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($mm);
        $count = (int)$queryBuilder
          ->count('*')
          ->from($mm)
          ->where($queryBuilder->expr()->eq($mmField, $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT)))
          ->executeQuery()
          ->fetchOne();
        $this->connectionPool->getConnectionForTable($table)->update($table, [$field => $count], ['uid' => $uid]);
      }
    }
  }

  private function countMembersWithoutType(): int
  {
    $queryBuilder = $this->queryBuilder(self::TABLE_MEMBER);

    return (int)$queryBuilder
      ->count('uid')
      ->from(self::TABLE_MEMBER)
      ->where($queryBuilder->expr()->eq('membership_type', 0))
      ->executeQuery()
      ->fetchOne();
  }

  private function countJournalEntriesWithoutLevel(): int
  {
    $queryBuilder = $this->queryBuilder(self::TABLE_JOURNAL);

    return (int)$queryBuilder
      ->count('uid')
      ->from(self::TABLE_JOURNAL)
      ->where(...$this->journalWithoutLevelConstraints($queryBuilder, self::TABLE_JOURNAL))
      ->executeQuery()
      ->fetchOne();
  }

  /**
   * @return list<string>
   */
  private function journalWithoutLevelConstraints(QueryBuilder $queryBuilder, string $alias): array
  {
    return [
      $queryBuilder->expr()->eq($alias . '.entry_type', $queryBuilder->createNamedParameter(MemberJournalEntry::ENTRY_TYPE_LEVEL_CHANGE)),
      $queryBuilder->expr()->eq($alias . '.new_member_level', 0),
    ];
  }

  /**
   * @return list<Site> Sites mit Clubmanager-Set ohne eigene Startwerte
   */
  private function sitesWithoutDefaults(): array
  {
    $sites = [];
    foreach ($this->clubmanagerSites() as $site) {
      if ((int)$site->getSettings()->get(MembershipMasterDataService::SETTING_DEFAULT_TYPE, 0) <= 0) {
        $sites[] = $site;
      }
    }

    return $sites;
  }

  /**
   * @return list<Site>
   */
  private function clubmanagerSites(): array
  {
    $sites = [];
    foreach ($this->siteFinder->getAllSites(false) as $site) {
      if ($site->getSettings()->has(MembershipMasterDataService::SETTING_DEFAULT_TYPE)) {
        $sites[] = $site;
      }
    }

    return $sites;
  }

  /**
   * Ohne Einschränkungen: gelöschte Mitglieder und Einträge werden mit migriert.
   */
  private function queryBuilder(string $table): QueryBuilder
  {
    $queryBuilder = $this->connectionPool->getQueryBuilderForTable($table);
    $queryBuilder->getRestrictions()->removeAll();

    return $queryBuilder;
  }
}
