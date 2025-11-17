<?php

namespace Quicko\Clubmanager\Updates;

use DateTime;
use Quicko\Clubmanager\Domain\Model\Member;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Updates\ChattyInterface;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\ReferenceIndexUpdatedPrerequisite;
use Symfony\Component\Console\Output\OutputInterface;

class MemberStatusHistoryUpgradeWizard implements UpgradeWizardInterface, ChattyInterface
{
  protected OutputInterface $output;

  public function setOutput(OutputInterface $output): void
  {
    $this->output = $output;
  }

  public function getIdentifier(): string
  {
    return 'clubmanagerMemberStatusHistory';
  }

  public function getTitle(): string
  {
    return 'Clubmanager: Migriere Member-Status zu Historie';
  }

  public function getDescription(): string
  {
    return 'Erstellt für alle bestehenden Members initiale StatusChange-Records basierend auf ihren aktuellen Status-Feldern (state, starttime, endtime).';
  }

  public function updateNecessary(): bool
  {
    $connection = GeneralUtility::makeInstance(ConnectionPool::class)
      ->getConnectionForTable('tx_clubmanager_domain_model_member');

    // Prüfe, ob es Members gibt, die noch keine StatusChanges haben
    $sql = "
      SELECT COUNT(*) 
      FROM tx_clubmanager_domain_model_member m
      WHERE m.deleted = 0
        AND m.state > 1
        AND NOT EXISTS (
          SELECT 1 
          FROM tx_clubmanager_domain_model_memberstatuschange sc
          WHERE sc.member = m.uid 
            AND sc.deleted = 0
        )
    ";

    $result = $connection->executeQuery($sql)->fetchOne();

    return $result > 0;
  }

  public function executeUpdate(): bool
  {
    $connection = GeneralUtility::makeInstance(ConnectionPool::class)
      ->getConnectionForTable('tx_clubmanager_domain_model_member');

    // Nur Members holen, die noch keine StatusChanges haben
    $sql = "
      SELECT m.uid, m.pid, m.state, m.starttime, m.endtime, m.crdate
      FROM tx_clubmanager_domain_model_member m
      WHERE m.deleted = 0
        AND m.state > 1
        AND NOT EXISTS (
          SELECT 1 
          FROM tx_clubmanager_domain_model_memberstatuschange sc
          WHERE sc.member = m.uid 
            AND sc.deleted = 0
        )
    ";

    $members = $connection->executeQuery($sql)->fetchAllAssociative();

    $this->output->writeln(sprintf('Verarbeite %d Members ohne StatusChanges...', count($members)));

    // Debug: Zähle States
    $stateCounts = [];
    foreach ($members as $m) {
      $s = (int) $m['state'];
      $stateCounts[$s] = ($stateCounts[$s] ?? 0) + 1;
    }
    $this->output->writeln(sprintf(
      '  State-Verteilung: ACTIVE=%d, SUSPENDED=%d, CANCELLED=%d',
      $stateCounts[Member::STATE_ACTIVE] ?? 0,
      $stateCounts[Member::STATE_SUSPENDED] ?? 0,
      $stateCounts[Member::STATE_CANCELLED] ?? 0
    ));

    $statusChangeConnection = GeneralUtility::makeInstance(ConnectionPool::class)
      ->getConnectionForTable('tx_clubmanager_domain_model_memberstatuschange');

    $createdCount = 0;

    foreach ($members as $member) {
      $memberUid = (int) $member['uid'];
      $state = (int) $member['state'];
      $starttime = (int) $member['starttime'];
      $endtime = (int) $member['endtime'];
      $crdate = (int) $member['crdate'];
      $pid = (int) $member['pid'];

      // Strategie: Erstelle vollständige StatusChange-Historie

      // Skip UNSET und APPLIED (sollte durch SQL-Query schon gefiltert sein)
      if ($state < Member::STATE_ACTIVE) {
        $this->output->writeln(sprintf('  SKIP: Member UID %d hat State %d (< %d)', $memberUid, $state, Member::STATE_ACTIVE));
        continue;
      }

      if ($state === Member::STATE_ACTIVE) {
        // STATE_ACTIVE: Ein StatusChange mit starttime
        $effectiveDate = $starttime > 0 ? $starttime : $crdate;

        $statusChangeConnection->insert(
          'tx_clubmanager_domain_model_memberstatuschange',
          [
            'pid' => $pid,
            'member' => $memberUid,
            'state' => $state,
            'effective_date' => $effectiveDate,
            'note' => 'Initiale Migration aus bestehendem Member-Status',
            'processed' => 1,
            'created_by' => 0,
            'crdate' => time(),
            'tstamp' => time(),
          ]
        );

        $createdCount++;

      } elseif ($state === Member::STATE_SUSPENDED) {
        // STATE_SUSPENDED: ZWEI StatusChanges erstellen

        $this->output->writeln(sprintf('  Verarbeite ruhenden Member UID %d (starttime: %d)', $memberUid, $starttime));

        // 1. Erst STATE_ACTIVE mit starttime
        $activeDate = $starttime > 0 ? $starttime : $crdate;

        $statusChangeConnection->insert(
          'tx_clubmanager_domain_model_memberstatuschange',
          [
            'pid' => $pid,
            'member' => $memberUid,
            'state' => Member::STATE_ACTIVE,
            'effective_date' => $activeDate,
            'note' => 'Initiale Migration: Ursprünglicher Aktiv-Status',
            'processed' => 1,
            'created_by' => 0,
            'crdate' => time(),
            'tstamp' => time(),
          ]
        );

        $createdCount++;

        // 2. Dann STATE_SUSPENDED mit now (da keine zeitliche Zuordnung möglich)
        $statusChangeConnection->insert(
          'tx_clubmanager_domain_model_memberstatuschange',
          [
            'pid' => $pid,
            'member' => $memberUid,
            'state' => Member::STATE_SUSPENDED,
            'effective_date' => time(),
            'note' => 'Initiale Migration: Ruhend-Status (zeitliche Zuordnung nicht möglich, Datum = Migration)',
            'processed' => 1,
            'created_by' => 0,
            'crdate' => time(),
            'tstamp' => time(),
          ]
        );

        $createdCount++;

      } elseif ($state === Member::STATE_CANCELLED) {
        // STATE_CANCELLED: ZWEI StatusChanges erstellen

        $this->output->writeln(sprintf('  Verarbeite gekündigten Member UID %d (starttime: %d, endtime: %d)', $memberUid, $starttime, $endtime));

        // 1. Erst STATE_ACTIVE mit starttime
        $activeDate = $starttime > 0 ? $starttime : $crdate;

        $statusChangeConnection->insert(
          'tx_clubmanager_domain_model_memberstatuschange',
          [
            'pid' => $pid,
            'member' => $memberUid,
            'state' => Member::STATE_ACTIVE,
            'effective_date' => $activeDate,
            'note' => 'Initiale Migration: Ursprünglicher Aktiv-Status',
            'processed' => 1,
            'created_by' => 0,
            'crdate' => time(),
            'tstamp' => time(),
          ]
        );

        $createdCount++;

        // 2. Dann STATE_CANCELLED mit endtime
        $cancelDate = $endtime > 0 ? $endtime : time();

        $statusChangeConnection->insert(
          'tx_clubmanager_domain_model_memberstatuschange',
          [
            'pid' => $pid,
            'member' => $memberUid,
            'state' => Member::STATE_CANCELLED,
            'effective_date' => $cancelDate,
            'note' => 'Initiale Migration: Kündigung',
            'processed' => 1,
            'created_by' => 0,
            'crdate' => time(),
            'tstamp' => time(),
          ]
        );

        $createdCount++;

      } else {
        // Unbekannter State - Debug-Ausgabe
        $this->output->writeln(sprintf('  WARNUNG: Member UID %d hat unbekannten State: %d', $memberUid, $state));
      }

      if ($createdCount % 100 === 0) {
        $this->output->writeln(sprintf('  %d StatusChanges erstellt...', $createdCount));
      }
    }

    $this->output->writeln(sprintf('Fertig! %d StatusChanges erstellt.', $createdCount));

    return true;
  }

  public function getPrerequisites(): array
  {
    return [
      DatabaseUpdatedPrerequisite::class,
      ReferenceIndexUpdatedPrerequisite::class,
    ];
  }
}

