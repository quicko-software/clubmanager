<?php

namespace Quicko\Clubmanager\Records\Mail;

use TYPO3\CMS\Core\SingletonInterface;

use Quicko\Clubmanager\Records\BaseRecordRepository;
use Quicko\Clubmanager\Domain\Model\Mail\Task;

class TaskRecordRepository extends BaseRecordRepository implements SingletonInterface
{

  /**
   * @var string
   */
  protected string $table = 'tx_clubmanager_domain_model_mail_task';

  /**
   * Returns a list with open tasks with a limit
   *
   * @param int|null $limit
   * @return array
   * @throws DBALDriverException
   * @throws DBALException|\Doctrine\DBAL\DBALException
   */
  public function getOpenSegment(int $limit = null)
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
      ->execute()
      ->fetchAllAssociative();
  }

  /**
   * Returns current count of open mailtasks
   *
   * @return int
   * @throws DBALException|\Doctrine\DBAL\DBALException
   * @throws DBALDriverException
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
    return (int)$queryBuilder
      ->execute()
      ->fetchOne();
  }

  /**
   * Returns current count of mail tasks with errors
   *
   * @return int
   * @throws DBALException|\Doctrine\DBAL\DBALException
   * @throws DBALDriverException
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

    return (int)$queryBuilder
      ->execute()
      ->fetchOne();
  }


  /**
   * Add mail task
   *
   * @param DataUpdateEventInterface $event
   * @throws DBALException|\Doctrine\DBAL\DBALException
   * @throws AspectNotFoundException
   */
  public function addMailTask(Task $task): void
  {

    $queryBuilder = $this->getQueryBuilder();
    $queryBuilder
      ->insert($this->table)
      ->values([
        'crdate' => $this->getExceptionTime(),
        'tstamp' => $this->getExceptionTime(),
        'pid' => $task->getPid(),
        'error_message' => $task->getErrorMessage() ?? "",
        'send_state' => $task->getSendState() ?? 0,
        'generator_class' => $task->getGeneratorClass(),
        'generator_arguments' => $task->getGeneratorArguments(),
        'processed_time' => $task->getProcessedTime(),
        'error_time' => $task->getErrorTime(),
        'open_tries' => $task->getOpenTries(),
        'priority_level' => $task->getPriorityLevel(),

      ])
      ->execute();
  }

  /**
   * Returns a list with all tasks
   *
   * @return array
   * @throws DBALDriverException
   * @throws DBALException|\Doctrine\DBAL\DBALException
   */
  public function getAll(bool $hideFinished = false)
  {
    $queryBuilder = $this->getQueryBuilder();
    $queryBuilder
      ->select('*')
      ->from($this->table)
      ->andWhere($queryBuilder->expr()->eq('deleted', 0))
      ->andWhere($queryBuilder->expr()->eq('hidden', 0))
      ;

    if($hideFinished) {
      $queryBuilder->andWhere($queryBuilder->expr()->neq('send_state', 1));
    }

    return $queryBuilder
      ->execute()
      ->fetchAllAssociative();
  }
}
