<?php

namespace Quicko\Clubmanager\Updates;

use DateTime;
use Quicko\Clubmanager\Domain\Model\Member;
use Quicko\Clubmanager\Domain\Model\MemberJournalEntry;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Updates\ChattyInterface;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\ReferenceIndexUpdatedPrerequisite;
use Symfony\Component\Console\Output\OutputInterface;

class MemberJournalInitialWizard implements UpgradeWizardInterface, ChattyInterface
{
  protected OutputInterface $output;

  public function setOutput(OutputInterface $output): void
  {
    $this->output = $output;
  }

  public function getIdentifier(): string
  {
    return 'clubmanagerMemberJournalInitial';
  }

  public function getTitle(): string
  {
    return 'Clubmanager: Initiale Member-Journal Einträge';
  }

  public function getDescription(): string
  {
    return 'Erstellt initiale Journal-Einträge für alle bestehenden Members basierend auf state, starttime, endtime.';
  }

  public function updateNecessary(): bool
  {
    $connection = GeneralUtility::makeInstance(ConnectionPool::class)
      ->getConnectionForTable('tx_clubmanager_domain_model_member');

    // Prüfe ob es Members ohne Journal-Einträge gibt
    $sql = "
      SELECT COUNT(*) 
      FROM tx_clubmanager_domain_model_member m
      WHERE m.deleted = 0
        AND m.state > 1
        AND NOT EXISTS (
          SELECT 1 
          FROM tx_clubmanager_domain_model_memberjournalentry j
          WHERE j.member = m.uid 
            AND j.deleted = 0
        )
    ";

    $result = $connection->executeQuery($sql)->fetchOne();

    return $result > 0;
  }

  public function executeUpdate(): bool
  {
    $connection = GeneralUtility::makeInstance(ConnectionPool::class)
      ->getConnectionForTable('tx_clubmanager_domain_model_member');

    // Hole Members OHNE Journal-Einträge
    $sql = "
      SELECT m.uid, m.pid, m.state, m.starttime, m.endtime, m.crdate, m.level
      FROM tx_clubmanager_domain_model_member m
      WHERE m.deleted = 0
        AND m.state > 1
        AND NOT EXISTS (
          SELECT 1 
          FROM tx_clubmanager_domain_model_memberjournalentry j
          WHERE j.member = m.uid AND j.deleted = 0
        )
    ";

    $members = $connection->executeQuery($sql)->fetchAllAssociative();

    $this->output->writeln(sprintf('Verarbeite %d Members ohne Journal-Einträge...', count($members)));

    $journalConnection = GeneralUtility::makeInstance(ConnectionPool::class)
      ->getConnectionForTable('tx_clubmanager_domain_model_memberjournalentry');

    $createdCount = 0;

    foreach ($members as $member) {
      $memberUid = (int)$member['uid'];
      $state = (int)$member['state'];
      $starttime = (int)$member['starttime'];
      $endtime = (int)$member['endtime'];
      $crdate = (int)$member['crdate'];
      $level = (int)$member['level'];
      $pid = (int)$member['pid'];

      if ($state < Member::STATE_ACTIVE) {
        continue;
      }

      // Für ALLE: Initiale Aktivierung
      $activationDate = $starttime > 0 ? $starttime : $crdate;
      $journalConnection->insert(
        'tx_clubmanager_domain_model_memberjournalentry',
        [
          'pid' => $pid,
          'member' => $memberUid,
          'entry_type' => MemberJournalEntry::ENTRY_TYPE_STATUS_CHANGE,
          'entry_date' => $activationDate,
          'target_state' => Member::STATE_ACTIVE,
          'effective_date' => $activationDate,
          'processed' => $activationDate,
          'creator_type' => MemberJournalEntry::CREATOR_TYPE_SYSTEM,
          'note' => 'Initiale Migration: Aktivierung',
          'crdate' => time(),
          'tstamp' => time(),
        ]
      );
      $createdCount++;

      // Für SUSPENDED: Zusätzlicher Eintrag
      if ($state === Member::STATE_SUSPENDED) {
        $journalConnection->insert(
          'tx_clubmanager_domain_model_memberjournalentry',
          [
            'pid' => $pid,
            'member' => $memberUid,
            'entry_type' => MemberJournalEntry::ENTRY_TYPE_STATUS_CHANGE,
            'entry_date' => time(),
            'target_state' => Member::STATE_SUSPENDED,
            'effective_date' => time(),
            'processed' => time(),
            'creator_type' => MemberJournalEntry::CREATOR_TYPE_SYSTEM,
            'note' => 'Initiale Migration: Ruhend-Status (zeitliche Zuordnung nicht möglich)',
            'crdate' => time(),
            'tstamp' => time(),
          ]
        );
        $createdCount++;
      }

      // Für CANCELLED: Zusätzlicher Eintrag
      if ($state === Member::STATE_CANCELLED) {
        $cancelDate = $endtime > 0 ? $endtime : time();

        $journalConnection->insert(
          'tx_clubmanager_domain_model_memberjournalentry',
          [
            'pid' => $pid,
            'member' => $memberUid,
            'entry_type' => MemberJournalEntry::ENTRY_TYPE_STATUS_CHANGE,
            'entry_date' => $cancelDate,
            'target_state' => Member::STATE_CANCELLED,
            'effective_date' => $cancelDate,
            'processed' => $cancelDate,
            'creator_type' => MemberJournalEntry::CREATOR_TYPE_SYSTEM,
            'note' => 'Initiale Migration: Kündigung',
            'crdate' => time(),
            'tstamp' => time(),
          ]
        );
        $createdCount++;
      }

      if ($createdCount % 50 === 0) {
        $this->output->writeln(sprintf('  %d Journal-Einträge erstellt...', $createdCount));
      }
    }

    $this->output->writeln(sprintf('Fertig! %d Journal-Einträge erstellt.', $createdCount));

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


