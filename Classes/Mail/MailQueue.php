<?php

namespace Quicko\Clubmanager\Mail;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use Quicko\Clubmanager\Domain\Model\Mail\Task;
use Quicko\Clubmanager\Records\Mail\TaskRecordRepository;
use Quicko\Clubmanager\Mail\Generator\Arguments\BaseMailGeneratorArguments;
use Quicko\Clubmanager\Mail\Generator\Arguments\MailGeneratorArgumentsSerializer;

class MailQueue
{
  public static function addMailTask(string $generatorClassName, BaseMailGeneratorArguments $arguments, int $priorityLevel = Task::PRIORITY_LEVEL_MIN)
  {
    $extConfig = GeneralUtility::makeInstance(ExtensionConfiguration::class);
    $mailTries = $extConfig->get('clubmanager','mailTries');
    $mailsPid = 0;

    /** @var TaskRecordRepository $repo */
    $repo = GeneralUtility::makeInstance(TaskRecordRepository::class);
    $seri_args = MailGeneratorArgumentsSerializer::serialize($arguments);

    $task = new Task();
    $task->setGeneratorArguments($seri_args);
    $task->setGeneratorClass($generatorClassName);
    $task->setOpenTries($mailTries);
    $task->setPid($mailsPid);
    $task->setPriorityLevel($priorityLevel);
    $repo->addMailTask($task);
  }
}
