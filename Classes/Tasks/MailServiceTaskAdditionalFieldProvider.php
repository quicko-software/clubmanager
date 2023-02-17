<?php

namespace Quicko\Clubmanager\Tasks;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Scheduler\AdditionalFieldProviderInterface;
use TYPO3\CMS\Scheduler\Controller\SchedulerModuleController;
use TYPO3\CMS\Scheduler\Task\AbstractTask;


class MailServiceTaskAdditionalFieldProvider implements AdditionalFieldProviderInterface
{
  public function getAdditionalFields(array &$taskInfo, $task, SchedulerModuleController $schedulerModule)
  {
    $VALUES = ($task !== null)
      ? $task->ARGUMENTS
      : MailServiceTask::ARGUMENT_DEFAULTS
    ;

    $additionalFields = array(
      'MAX_NUM_MAILS' => [
        'code' => '<input type="number" name="tx_scheduler[clubmanager.MailServiceTask.MAX_NUM_MAILS]" value="'.
          $VALUES['MAX_NUM_MAILS'].'" class="form-control" />',
        'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_be.xlf:task.MailServiceTask.arg.MAX_NUM_MAILS',
        'cshKey' => '',
        'cshLabel' => ''
      ],
    );

    return $additionalFields;
  }


  public function validateAdditionalFields(array &$submittedData, SchedulerModuleController $schedulerModule)
  {
    $submittedData['clubmanager.MailServiceTask.MAX_NUM_MAILS'] = intval(
      $submittedData['clubmanager.MailServiceTask.MAX_NUM_MAILS']
    );

    $maxNumMails = & $submittedData['clubmanager.MailServiceTask.MAX_NUM_MAILS'];
    $maxNumMails = intval($maxNumMails);
    if ($maxNumMails < 1) {
      $this->flashInvalidValueForFieldMessage('LLL:EXT:clubmanager/Resources/Private/Language/locallang_be.xlf:task.MailServiceTask.arg.MAX_NUM_MAILS');
      return false;
    }

    return true;
  }

  public function saveAdditionalFields(array $submittedData, AbstractTask $task)
  {
    $task->ARGUMENTS['MAX_NUM_MAILS'] = $submittedData['clubmanager.MailServiceTask.MAX_NUM_MAILS'];
  }

  private function flashInvalidValueForFieldMessage($lllFieldName) {
    $fieldLabel = LocalizationUtility::translate($lllFieldName,'clubmanager');

    $textTemplate = LocalizationUtility::translate('LLL:EXT:clubmanager/Resources/Private/Language/locallang_be.xlf:task.general.errors.invalid_value','clubmanager');
    $text = sprintf(
      $textTemplate,
      $fieldLabel,
    );
    $message = GeneralUtility::makeInstance(FlashMessage::class,
      $fieldLabel, $text, FlashMessage::ERROR, true
    );
    $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
    $flashMessageService->getMessageQueueByIdentifier()->addMessage($message);
  }

}
