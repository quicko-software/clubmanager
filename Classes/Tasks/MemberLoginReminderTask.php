<?php

namespace Quicko\Clubmanager\Tasks;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use Quicko\Clubmanager\Domain\Repository\MemberRepository;
use Quicko\Clubmanager\Utils\LogUtils;
use Quicko\Clubmanager\Utils\TypoScriptUtils;
use Quicko\Clubmanager\Mail\Generator\Arguments\MemberLoginReminderArguments;
use Quicko\Clubmanager\Mail\Generator\MemberLoginReminderGenerator;
use Quicko\Clubmanager\Mail\MailQueue;



class MemberLoginReminderTask extends \TYPO3\CMS\Scheduler\Task\AbstractTask
{
  const ARGUMENT_DEFAULTS = [
    'MIN_DAY_PERIOD' => 14,
    'MEMBER_PID_LIST' => '',
  ];

  public $ARGUMENTS = []; // will be filled by the ...AddtionalFieldProvider


  public function execute()
  {
    $MIN_DAY_PERIOD = $this->getArg('MIN_DAY_PERIOD');
    $MEMBER_PID_LIST = trim($this->getArg('MEMBER_PID_LIST'));
    $member_pid_list = $MEMBER_PID_LIST ? explode(',',$MEMBER_PID_LIST) : null; // explode on an empty string yields an array with one empty string element -> bad

    $memberRepo = $this->getMemberRepo();
    $memberList = $memberRepo->findMemberRoRemind($MIN_DAY_PERIOD, $member_pid_list);

    LogUtils::info(__CLASS__, 'Reminder loop begin ('.count($memberList).' member)');
    foreach ($memberList as $member) {

      $loginPidString = TypoScriptUtils::getTypoScriptValueForPage('plugin.tx_clubmanager.settings.feUsersLoginPid', $member->getPid());
      $loginPid = intval($loginPidString);

      $args = new MemberLoginReminderArguments();
      $args->memberUid = $member->getUid();
      $args->loginPid = $loginPid;
  
      MailQueue::addMailTask(MemberLoginReminderGenerator::class, $args);

      $now = time();
      $feUser = $member->getFeuser();
      if($feUser) {
        $feUser->setLastreminderemailsent($now);
        $memberRepo->update($member);
      } else {
        LogUtils::error(__CLASS__, 'Member ('.$member->getUid().' has no FeUser)');
      }
    }
    $memberRepo->persistAll();
    LogUtils::info(__CLASS__, 'Reminder loop end');
    
    return true;
  }

  private function getMemberRepo() {
    return GeneralUtility::makeInstance(MemberRepository::class);
  }

  
  private function getArg($argName) {
    if (array_key_exists($argName, $this->ARGUMENTS)) {
      return $this->ARGUMENTS[$argName];
    } else if (array_key_exists($argName, MemberLoginReminderTask::ARGUMENT_DEFAULTS)) {
      return self::ARGUMENT_DEFAULTS[$argName];
    }
    throw new \LogicException('bad argument name: '.$argName);
  }  
}
