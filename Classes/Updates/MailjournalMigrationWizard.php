<?php

declare(strict_types=1);

namespace Quicko\Clubmanager\Updates;

use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Registry;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\ChattyInterface;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

#[UpgradeWizard('clubmanager_mailjournalMigration')]
final class MailjournalMigrationWizard implements UpgradeWizardInterface, ChattyInterface
{
  private const REGISTRY_KEY = 'mailjournalMigrationDone';
  private const SOURCE_TABLE = 'tx_clubmanager_domain_model_mail_task';
  private const TARGET_TABLE = 'tx_mailjournal_domain_model_mail_task';
  private const OLD_SCHEDULER_CLASS = 'Quicko\\Clubmanager\\Tasks\\MailServiceTask';
  private const NEW_SCHEDULER_CLASS = 'Quicko\\Mailjournal\\Tasks\\MailServiceTask';

  /**
   * @var array<string, string>
   */
  private const GENERATOR_CLASS_MAP = [
    'Quicko\\Clubmanager\\Mail\\Generator\\GenericMailGenerator' => 'Quicko\\Clubmanager\\Mail\\Generator\\GenericMemberMailGenerator',
  ];

  protected OutputInterface $output;

  public function __construct(
    private readonly ConnectionPool $connectionPool,
    private readonly Registry $registry,
  ) {
  }

  public function setOutput(OutputInterface $output): void
  {
    $this->output = $output;
  }

  public function getIdentifier(): string
  {
    return 'clubmanager_mailjournalMigration';
  }

  public function getTitle(): string
  {
    return 'Clubmanager: Mail-Queue nach mailjournal übernehmen';
  }

  public function getDescription(): string
  {
    return 'Kopiert offene und historische Mail-Tasks von tx_clubmanager_domain_model_mail_task '
      . 'nach tx_mailjournal_domain_model_mail_task und stellt Scheduler-Tasks auf die mailjournal-Klasse um. '
      . 'Vorher Backup empfohlen. Danach kann die alte Tabelle im Database Analyzer entfernt werden.';
  }

  public function updateNecessary(): bool
  {
    if ($this->registry->get('clubmanager', self::REGISTRY_KEY, false)) {
      return false;
    }

    $hasQueueRows = $this->sourceTableExists() && $this->countSourceRows() > 0;

    return $hasQueueRows || $this->hasOldSchedulerTasks();
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

  public function executeUpdate(): bool
  {
    $copied = $this->copyQueueRows();
    $remapped = $this->remapSchedulerTasks();
    $this->output->writeln(sprintf('Mail-Tasks kopiert: %d. Scheduler-Tasks umgestellt: %d.', $copied, $remapped));
    if ($this->hasOldSchedulerTasks()) {
      $this->output->writeln(
        'Scheduler-Task-Klasse konnte nicht vollständig umgeschrieben werden. Wizard bleibt offen.'
      );

      return false;
    }
    $this->registry->set('clubmanager', self::REGISTRY_KEY, true);

    return true;
  }

  private function sourceTableExists(): bool
  {
    $connection = $this->connectionPool->getConnectionForTable(self::TARGET_TABLE);

    return $connection->createSchemaManager()->tablesExist([self::SOURCE_TABLE]);
  }

  private function countSourceRows(): int
  {
    if (!$this->sourceTableExists()) {
      return 0;
    }
    $connection = $this->connectionPool->getConnectionForTable(self::SOURCE_TABLE);

    return (int) $connection->fetchOne('SELECT COUNT(*) FROM ' . self::SOURCE_TABLE);
  }

  private function copyQueueRows(): int
  {
    if (!$this->sourceTableExists()) {
      return 0;
    }

    $source = $this->connectionPool->getConnectionForTable(self::SOURCE_TABLE);
    $target = $this->connectionPool->getConnectionForTable(self::TARGET_TABLE);
    $sharedColumns = $this->sharedColumns($source, $target);
    $rows = $source->fetchAllAssociative('SELECT * FROM ' . self::SOURCE_TABLE);
    $copied = 0;

    foreach ($rows as $row) {
      $generatorClass = (string) ($row['generator_class'] ?? '');
      $generatorClass = self::GENERATOR_CLASS_MAP[$generatorClass] ?? $generatorClass;
      $arguments = (string) ($row['generator_arguments'] ?? '');
      $crdate = $row['crdate'] ?? 0;
      $exists = $target->fetchOne(
        'SELECT uid FROM ' . self::TARGET_TABLE
          . ' WHERE generator_class = ? AND generator_arguments = ? AND crdate = ?',
        [$generatorClass, $arguments, $crdate]
      );
      if ($exists) {
        continue;
      }

      $insert = [];
      foreach ($sharedColumns as $column) {
        if ($column === 'uid') {
          continue;
        }
        $insert[$column] = $row[$column] ?? null;
      }
      $insert['generator_class'] = $generatorClass;
      $target->insert(self::TARGET_TABLE, $insert);
      ++$copied;
    }

    return $copied;
  }

  /**
   * @return string[]
   */
  private function sharedColumns(Connection $source, Connection $target): array
  {
    return array_values(array_intersect(
      $this->columnNames($source, self::SOURCE_TABLE),
      $this->columnNames($target, self::TARGET_TABLE)
    ));
  }

  /**
   * @return string[]
   */
  private function columnNames(Connection $connection, string $table): array
  {
    $schemaManager = $connection->createSchemaManager();
    if (method_exists($schemaManager, 'introspectTable')) {
      return array_keys($schemaManager->introspectTable($table)->getColumns());
    }

    return array_map(
      static fn ($col) => $col->getName(),
      $schemaManager->listTableColumns($table)
    );
  }

  private function hasOldSchedulerTasks(): bool
  {
    return $this->remapSchedulerTasks(dryRun: true) > 0;
  }

  /**
   * PHP serialize stores backslashes in the class name. MySQL LIKE treats
   * `\` as escape, so matching in SQL is unreliable — filter in PHP.
   */
  private function remapSchedulerTasks(bool $dryRun = false): int
  {
    $connection = $this->connectionPool->getConnectionForTable('tx_scheduler_task');
    if (!$connection->createSchemaManager()->tablesExist(['tx_scheduler_task'])) {
      return 0;
    }

    $rows = $connection->fetchAllAssociative('SELECT uid, serialized_task_object FROM tx_scheduler_task');
    $updated = 0;
    $fromToken = 'O:' . strlen(self::OLD_SCHEDULER_CLASS) . ':"' . self::OLD_SCHEDULER_CLASS . '"';
    $toToken = 'O:' . strlen(self::NEW_SCHEDULER_CLASS) . ':"' . self::NEW_SCHEDULER_CLASS . '"';

    foreach ($rows as $row) {
      $serialized = (string) $row['serialized_task_object'];
      if (!str_contains($serialized, self::OLD_SCHEDULER_CLASS)) {
        continue;
      }
      if ($dryRun) {
        ++$updated;
        continue;
      }
      $newSerialized = str_replace($fromToken, $toToken, $serialized);
      if ($newSerialized === $serialized) {
        continue;
      }
      $connection->update(
        'tx_scheduler_task',
        ['serialized_task_object' => $newSerialized],
        ['uid' => (int) $row['uid']]
      );
      ++$updated;
    }

    return $updated;
  }
}
