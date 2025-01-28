<?php

namespace Quicko\Clubmanager\Tasks;

use Exception;
use LogicException;
use Quicko\Clubmanager\Domain\Model\Mail\Task;
use Quicko\Clubmanager\Mail\MailSendService;
use Quicko\Clubmanager\Records\Mail\TaskRecordRepository;
use Throwable;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

class MailServiceTask extends AbstractTask
{
  public const ARGUMENT_DEFAULTS = [
    'MAX_NUM_MAILS' => 20,
  ];

  public array $ARGUMENTS = [];

  public function execute(): bool
  {
    $maxNumMails = $this->getArg('MAX_NUM_MAILS');
    $openSegment = $this->getTaskRepo()->getOpenSegment(intval($maxNumMails));
    $sendService = new MailSendService();
    $result = true;
    foreach ($openSegment as $item) {
      try {
        $openTries = intval($item['open_tries']);
        if ($openTries <= 0) {
          $this->getTaskRepo()->update([$item['uid']], [
            'send_state' => Task::SEND_STATE_STOPPED,
          ]);
          continue;
        }

        $mailSendSuccess = $sendService->processMailByGenerator($item['generator_class'], $item['generator_arguments']);

        $newData = [
          'send_state' => Task::SEND_STATE_DONE,
          'processed_time' => date('Y-m-d H:i:s'),
        ];
        if (!$mailSendSuccess) {
          $newData['error_message'] = 'E-mail could not be generated';
          $newData['error_time'] = date('Y-m-d H:i:s');
        }
        $this->getTaskRepo()->update([$item['uid']], $newData);
      } catch (Throwable $e) {
        $this->handleError($item, $e);
        $result = false;
      } 
    }

    return $result;
  }

  private function handleError(array $item, Throwable $e): void
  {
    $openTries = intval($item['open_tries']) - 1;
    $send_state = $item['send_state'];
    if ($openTries <= 0) {
      $send_state = Task::SEND_STATE_STOPPED;
    }
    $errorMessage = $e . "\n------------------------PREVIOUS MESSAGE------------------------\n" . $item['error_message'];
    $errorMessage = substr($errorMessage, 0, 64000).' [...]';
    $this->getTaskRepo()->update([$item['uid']], [
      'error_message' => $errorMessage,
      'error_time' => date('Y-m-d H:i:s'),
      'processed_time' => date('Y-m-d H:i:s'),
      'open_tries' => $openTries,
      'send_state' => $send_state,
    ]);
  }

  /**
   * Return the TaskRecordRepository.
   */
  private function getTaskRepo(): TaskRecordRepository
  {
    /** @var TaskRecordRepository $taskRecordRepository */
    $taskRecordRepository = GeneralUtility::makeInstance(TaskRecordRepository::class);

    return $taskRecordRepository;
  }

  /**
   * @return string Information to display
   *
   */
  public function getAdditionalInformation(): string
  {
    $message = LocalizationUtility::translate(
      'LLL:EXT:clubmanager/Resources/Private/Language/locallang_be.xlf:task.MailServiceTask.statusMsg'
    );

    $mailsOpen = $this->getTaskRepo()->countMailsOpen();
    $mailsWithErrors = $this->getTaskRepo()->countMailsWithErrors();
    $maxNumMails = $this->getArg('MAX_NUM_MAILS');

    return sprintf(
      $message,
      $mailsOpen,
      $mailsWithErrors,
      $maxNumMails
    );
  }

  /**
   * @return string|int
   */
  private function getArg(string $argName)
  {
    if (array_key_exists($argName, $this->ARGUMENTS)) {
      return $this->ARGUMENTS[$argName];
    } elseif (array_key_exists($argName, MailServiceTask::ARGUMENT_DEFAULTS)) {
      return MailServiceTask::ARGUMENT_DEFAULTS[$argName];
    }
    throw new LogicException('bad argument name: ' . $argName);
  }
}
