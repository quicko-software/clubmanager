<?php

namespace Quicko\Clubmanager\Records\Mail;

use Quicko\Clubmanager\Domain\Model\Mail\Task;
use Quicko\Clubmanager\Records\BaseRecordRepository;
use TYPO3\CMS\Core\SingletonInterface;

class TaskRecordRepository extends BaseRecordRepository implements SingletonInterface
{
  protected string $table = 'tx_clubmanager_domain_model_mail_task';

  /**
   * Returns a list with open tasks with a limit.
   *
   * @return array
   *
   */
  public function getOpenSegment(?int $limit = null)
  {
    $queryBuilder = $this->getQueryBuilder();
    $queryBuilder
      ->select('*')
      ->from($this->table)
      ->andWhere($queryBuilder->expr()->eq('deleted', 0))
      ->andWhere($queryBuilder->expr()->eq('hidden', 0))
      ->andWhere($queryBuilder->expr()->eq('send_state', Task::SEND_STATE_WILL_SEND))
      ->orderBy('priority_level', 'DESC')
      ->addOrderBy('uid', 'ASC')
    ;

    if ($limit !== null) {
      $queryBuilder->setMaxResults($limit);
    }

    return $queryBuilder
      ->executeQuery()
      ->fetchAllAssociative();
  }

  /**
   * Returns current count of open mailtasks.
   *
   */
  public function countMailsOpen(): int
  {
    $queryBuilder = $this->getQueryBuilder();
    $queryBuilder
      ->count('*')
      ->from($this->table)
      ->andWhere($queryBuilder->expr()->eq('deleted', 0))
      ->andWhere($queryBuilder->expr()->eq('hidden', 0))
      ->andWhere($queryBuilder->expr()->eq('send_state', Task::SEND_STATE_WILL_SEND))
    ;

    return (int) $queryBuilder
      ->executeQuery()
      ->fetchOne();
  }

  /**
   * Returns current count of mail tasks with errors.
   *
   */
  public function countMailsWithErrors(): int
  {
    $queryBuilder = $this->getQueryBuilder();
    $queryBuilder
      ->count('*')
      ->from($this->table)
      ->andWhere($queryBuilder->expr()->eq('deleted', 0))
      ->andWhere($queryBuilder->expr()->eq('hidden', 0))
      ->andWhere($queryBuilder->expr()->neq('send_state', Task::SEND_STATE_DONE))
      ->andWhere("error_message is NOT NULL AND error_message <> ''");

    return (int) $queryBuilder
      ->executeQuery()
      ->fetchOne();
  }

  public function addMailTask(Task $task): void
  {
    $queryBuilder = $this->getQueryBuilder();
    $queryBuilder
      ->insert($this->table)
      ->values([
        'crdate' => $this->getExceptionTime(),
        'tstamp' => $this->getExceptionTime(),
        'pid' => $task->getPid(),
        'error_message' => $task->getErrorMessage(),
        'send_state' => $task->getSendState(),
        'generator_class' => $task->getGeneratorClass(),
        'generator_arguments' => $task->getGeneratorArguments(),
        'processed_time' => $task->getProcessedTime(),
        'error_time' => $task->getErrorTime(),
        'open_tries' => $task->getOpenTries(),
        'priority_level' => $task->getPriorityLevel(),
      ])
      ->executeStatement();
  }

  /**
   * Returns a list with all tasks.
   *
   * @return array<mixed>
   */
  public function getAll(bool $hideFinished = false): array
  {
    $queryBuilder = $this->getQueryBuilder();
    $queryBuilder
      ->select('*')
      ->from($this->table)
      ->andWhere($queryBuilder->expr()->eq('deleted', 0))
      ->andWhere($queryBuilder->expr()->eq('hidden', 0))
    ;

    if ($hideFinished) {
      $queryBuilder->andWhere($queryBuilder->expr()->neq('send_state', 1));
    }

    return $queryBuilder
      ->executeQuery()
      ->fetchAllAssociative();
  }
}
