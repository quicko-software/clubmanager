<?php

namespace Quicko\Clubmanager\Tasks;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Scheduler\AdditionalFieldProviderInterface;
use TYPO3\CMS\Scheduler\Controller\SchedulerModuleController;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

use Quicko\Clubmanager\Tasks\MemberLoginReminderTask;


class MemberLoginReminderTaskAdditionalFieldProvider implements AdditionalFieldProviderInterface
{
    /**
     * Gets additional fields to render in the form to add/edit a task
     *
     * @param array $taskInfo Values of the fields from the add/edit task form
     * @param MemberLoginReminderTask|null $task The task object being edited. Null when adding a task!
     * @param \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $schedulerModule Reference to the scheduler backend module
     * @return array A two dimensional array: array('fieldId' => array('code' => '', 'label' => '', 'cshKey' => '', 'cshLabel' => ''))
     */
  public function getAdditionalFields(array &$taskInfo, $task, SchedulerModuleController $schedulerModule)
  {
    $VALUES = ($task !== null)
      ? $task->ARGUMENTS
      : MemberLoginReminderTask::ARGUMENT_DEFAULTS
    ;


    $additionalFields = array(
      'MIN_DAY_PERIOD' => [
        'code' => '<input type="number" name="tx_scheduler[clubmanager.MemberLoginReminderTask.MIN_DAY_PERIOD]" value="'.
          $VALUES['MIN_DAY_PERIOD'].'" class="form-control" />',
        'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_be.xlf:task.MemberLoginReminderTask.arg.MIN_DAY_PERIOD',
        'cshKey' => '',
        'cshLabel' => ''
      ],
      'MEMBER_PID_LIST' => [
        'code' => '<input name="tx_scheduler[clubmanager.MemberLoginReminderTask.MEMBER_PID_LIST]" value="'.
          $VALUES['MEMBER_PID_LIST'].'" class="form-control" />',
        'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_be.xlf:task.MemberLoginReminderTask.arg.MEMBER_PID_LIST',
        'cshKey' => '',
        'cshLabel' => ''
      ]
    );

    return $additionalFields;
  }


  public function validateAdditionalFields(array &$submittedData, SchedulerModuleController $schedulerModule)
  {
    $submittedData['clubmanager.MemberLoginReminderTask.MIN_DAY_PERIOD'] = intval(
      $submittedData['clubmanager.MemberLoginReminderTask.MIN_DAY_PERIOD']
    );
    $submittedData['clubmanager.MemberLoginReminderTask.MEMBER_PID_LIST'] = implode(
      ',',
      GeneralUtility::intExplode(',',
        $submittedData['clubmanager.MemberLoginReminderTask.MEMBER_PID_LIST'],
        true
      )
    );

    return true;
  }


  public function saveAdditionalFields(array $submittedData, AbstractTask $task): void
  {
    /**
     * @var MemberLoginReminderTask $memberLoginReminderTask
     */
    $memberLoginReminderTask = $task;
    $memberLoginReminderTask->ARGUMENTS['MIN_DAY_PERIOD'] = $submittedData['clubmanager.MemberLoginReminderTask.MIN_DAY_PERIOD'];
    $memberLoginReminderTask->ARGUMENTS['MEMBER_PID_LIST'] = $submittedData['clubmanager.MemberLoginReminderTask.MEMBER_PID_LIST'];
  }

}
