<?php

declare(strict_types=1);

namespace Quicko\Clubmanager\Updates;

use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\ChattyInterface;
use TYPO3\CMS\Install\Updates\ConfirmableInterface;
use TYPO3\CMS\Install\Updates\Confirmation;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

#[UpgradeWizard('clubmanager_restoreDeletedJournalEntries')]
final class RestoreDeletedJournalEntriesWizard implements UpgradeWizardInterface, ChattyInterface, ConfirmableInterface
{
  private const TABLE_JOURNAL = 'tx_clubmanager_domain_model_memberjournalentry';
  private const TABLE_MEMBER = 'tx_clubmanager_domain_model_member';

  protected OutputInterface $output;
  protected Confirmation $confirmation;

  public function __construct(
    private readonly ConnectionPool $connectionPool,
  ) {
    $this->confirmation = new Confirmation(
      'Gelöschte Journal-Einträge wiederherstellen?',
      'Setzt deleted=0 auf MemberJournalEntry-Einträge, die durch Cascade-Delete '
        . 'beim Löschen des zugehörigen Members fälschlich mitgelöscht wurden. '
        . 'Empfehlung: Vorher ein Datenbank-Backup erstellen.',
      false,
      'Ja, wiederherstellen',
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
    return 'Clubmanager: Fälschlich gelöschte Journal-Einträge wiederherstellen';
  }

  public function getDescription(): string
  {
    return 'Stellt MemberJournalEntry-Einträge wieder her, die durch IRRE-Cascade-Delete '
      . 'beim Soft-Delete eines Members fälschlich auf deleted=1 gesetzt wurden. '
      . 'Betrifft nur Einträge, deren zugehöriger Member ebenfalls deleted=1 ist.';
  }

  public function updateNecessary(): bool
  {
    return $this->countAffectedEntries() > 0;
  }

  public function executeUpdate(): bool
  {
    $count = $this->countAffectedEntries();
    $this->output->writeln(sprintf('Gefundene betroffene Journal-Einträge: %d', $count));

    if ($count === 0) {
      $this->output->writeln('Keine Einträge zum Wiederherstellen gefunden.');
      return true;
    }

    $connection = $this->connectionPool->getConnectionForTable(self::TABLE_JOURNAL);
    $updated = $connection->executeStatement(
      'UPDATE ' . self::TABLE_JOURNAL . ' j'
        . ' INNER JOIN ' . self::TABLE_MEMBER . ' m ON m.uid = j.member'
        . ' SET j.deleted = 0'
        . ' WHERE j.deleted = 1 AND m.deleted = 1'
    );

    $this->output->writeln(sprintf('Wiederhergestellt: %d Journal-Einträge.', $updated));

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

  private function countAffectedEntries(): int
  {
    $connection = $this->connectionPool->getConnectionForTable(self::TABLE_JOURNAL);

    return (int) $connection->executeQuery(
      'SELECT COUNT(*)'
        . ' FROM ' . self::TABLE_JOURNAL . ' j'
        . ' INNER JOIN ' . self::TABLE_MEMBER . ' m ON m.uid = j.member'
        . ' WHERE j.deleted = 1 AND m.deleted = 1'
    )->fetchOne();
  }
}
