<?php

declare(strict_types=1);

namespace Quicko\Clubmanager\Updates;

use Quicko\Clubmanager\Domain\Model\Member;
use Quicko\Clubmanager\Domain\Model\MemberJournalEntry;
use Quicko\Clubmanager\Utils\SettingUtils;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\ChattyInterface;
use TYPO3\CMS\Install\Updates\ConfirmableInterface;
use TYPO3\CMS\Install\Updates\Confirmation;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

#[UpgradeWizard('clubmanager_createMissingCancelledJournalEntries')]
final class CreateMissingCancelledJournalEntriesWizard implements UpgradeWizardInterface, ChattyInterface, ConfirmableInterface
{
  private const TABLE_JOURNAL = 'tx_clubmanager_domain_model_memberjournalentry';
  private const TABLE_MEMBER = 'tx_clubmanager_domain_model_member';

  protected OutputInterface $output;
  protected Confirmation $confirmation;

  public function __construct(
    private readonly ConnectionPool $connectionPool,
  ) {
    $this->confirmation = new Confirmation(
      'Fehlende Kündigungs-Einträge nachlegen?',
      'Erstellt MemberJournalEntry-Einträge vom Typ "status_change" mit target_state=CANCELLED '
        . 'für Members, die als gekündigt markiert sind aber keinen entsprechenden Journal-Eintrag haben. '
        . 'Betrifft auch bereits gelöschte Members (deleted=1). '
        . 'Empfehlung: Vorher ein Datenbank-Backup erstellen.',
      false,
      'Ja, Einträge erstellen',
      'Nein',
      false
    );
  }

  public function setOutput(OutputInterface $output): void
  {
    $this->output = $output;
  }

  public function getConfirmation(): Confirmation
  {
    return $this->confirmation;
  }

  public function getTitle(): string
  {
    return 'Clubmanager: Fehlende Kündigungs-Journal-Einträge nachlegen';
  }

  public function getDescription(): string
  {
    return 'Erstellt fehlende "Gekündigt"-MemberJournalEntry-Einträge für Members mit state=CANCELLED, '
      . 'die keinen entsprechenden Journal-Eintrag besitzen. Betrifft Members aus der Zeit vor der '
      . 'Journal-Einführung, sowohl aktive als auch bereits gelöschte.';
  }

  public function updateNecessary(): bool
  {
    return count($this->findAffectedMembers()) > 0;
  }

  public function executeUpdate(): bool
  {
    $members = $this->findAffectedMembers();

    $this->output->writeln(sprintf('Gefundene Members ohne Kündigungs-Eintrag: %d', count($members)));

    if (count($members) === 0) {
      $this->output->writeln('Keine fehlenden Einträge gefunden.');
      return true;
    }

    $journalConnection = $this->connectionPool->getConnectionForTable(self::TABLE_JOURNAL);
    $createdCount = 0;

    foreach ($members as $member) {
      $memberUid = (int) $member['uid'];
      $memberPid = (int) $member['pid'];
      $endtime = (int) $member['endtime'];
      $tstamp = (int) $member['tstamp'];

      $cancelDate = $endtime > 0 ? $endtime : $tstamp;
      $journalPid = $this->resolveJournalStoragePid($memberPid);

      $journalConnection->insert(
        self::TABLE_JOURNAL,
        [
          'pid' => $journalPid,
          'member' => $memberUid,
          'entry_type' => MemberJournalEntry::ENTRY_TYPE_STATUS_CHANGE,
          'entry_date' => $cancelDate,
          'target_state' => Member::STATE_CANCELLED,
          'effective_date' => $cancelDate,
          'processed' => $cancelDate,
          'creator_type' => MemberJournalEntry::CREATOR_TYPE_SYSTEM,
          'note' => 'Migration: Fehlender Kündigungseintrag nachgetragen',
          'crdate' => time(),
          'tstamp' => time(),
        ]
      );
      $createdCount++;

      if ($createdCount % 50 === 0) {
        $this->output->writeln(sprintf('  %d Einträge erstellt...', $createdCount));
      }
    }

    $this->output->writeln(sprintf('Fertig! %d Kündigungs-Einträge erstellt.', $createdCount));

    return true;
  }

  /**
   * @return string[]
   */
  public function getPrerequisites(): array
  {
    return [
      DatabaseUpdatedPrerequisite::class,
    ];
  }

  /**
   * Findet Members mit state=CANCELLED ohne Kuendigungs-JournalEntry.
   * Prueft unabhaengig vom deleted-Status des JournalEntry, um Duplikate
   * zu vermeiden falls der RestoreDeletedJournalEntriesWizard noch nicht lief.
   *
   * @return array<int, array<string, mixed>>
   */
  private function resolveJournalStoragePid(int $memberPid): int
  {
    if ($memberPid <= 0) {
      return 0;
    }

    $storagePid = (int) SettingUtils::getSiteSetting($memberPid, 'clubmanager.memberJournalStoragePid', 0);

    return $storagePid > 0 ? $storagePid : $memberPid;
  }

  private function findAffectedMembers(): array
  {
    $connection = $this->connectionPool->getConnectionForTable(self::TABLE_MEMBER);

    return $connection->executeQuery(
      'SELECT m.uid, m.pid, m.endtime, m.tstamp'
        . ' FROM ' . self::TABLE_MEMBER . ' m'
        . ' WHERE m.state = ' . Member::STATE_CANCELLED
        . ' AND NOT EXISTS ('
        . '   SELECT 1 FROM ' . self::TABLE_JOURNAL . ' j'
        . '   WHERE j.member = m.uid'
        . '     AND j.entry_type = ' . $connection->quote(MemberJournalEntry::ENTRY_TYPE_STATUS_CHANGE)
        . '     AND j.target_state = ' . Member::STATE_CANCELLED
        . ' )'
    )->fetchAllAssociative();
  }
}
