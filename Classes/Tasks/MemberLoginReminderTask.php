<?php

namespace Quicko\Clubmanager\Tasks;

use LogicException;
use Quicko\Clubmanager\Domain\Repository\MemberRepository;
use Quicko\Clubmanager\Mail\Generator\Arguments\MemberLoginReminderArguments;
use Quicko\Clubmanager\Mail\Generator\MemberLoginReminderGenerator;
use Quicko\Clubmanager\Mail\MailQueue;
use Quicko\Clubmanager\Utils\LogUtils;
use Quicko\Clubmanager\Utils\TypoScriptUtils;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class MemberLoginReminderTask extends \TYPO3\CMS\Scheduler\Task\AbstractTask
{
  public const ARGUMENT_DEFAULTS = [
    'MIN_DAY_PERIOD' => 14,
    'MEMBER_PID_LIST' => '',
  ];

  public array $ARGUMENTS = []; // will be filled by the ...AddtionalFieldProvider

  public function execute(): bool
  {
    $MIN_DAY_PERIOD = intval($this->getArg('MIN_DAY_PERIOD'));
    $MEMBER_PID_LIST = trim((string)$this->getArg('MEMBER_PID_LIST'));
    $member_pid_list = $MEMBER_PID_LIST ? explode(',', $MEMBER_PID_LIST) : null; // explode on an empty string yields an array with one empty string element -> bad

    $memberRepo = $this->getMemberRepo();
    $memberList = $memberRepo->findMemberRoRemind($MIN_DAY_PERIOD, $member_pid_list);

    LogUtils::info(__CLASS__, 'Reminder loop begin (' . count($memberList) . ' member)');
    foreach ($memberList as $member) {
      $loginPidString = TypoScriptUtils::getTypoScriptValueForPage('plugin.tx_clubmanager.settings.feUsersLoginPid', $member->getPid());
      $loginPid = intval($loginPidString);

      $args = new MemberLoginReminderArguments();
      $args->memberUid = $member->getUid();
      $args->loginPid = $loginPid;

      MailQueue::addMailTask(MemberLoginReminderGenerator::class, $args);

      $now = time();
      $feUser = $member->getFeuser();
      if ($feUser) {
        $feUser->setLastreminderemailsent($now);
        $memberRepo->update($member);
      } else {
        LogUtils::error(__CLASS__, 'Member (' . $member->getUid() . ' has no FeUser)');
      }
    }
    $memberRepo->persistAll();
    LogUtils::info(__CLASS__, 'Reminder loop end');

    return true;
  }

  private function getMemberRepo(): MemberRepository
  {
    /** @var MemberRepository $memberRepository */
    $memberRepository = GeneralUtility::makeInstance(MemberRepository::class);

    return $memberRepository;
  }

  private function getArg(string $argName): string|int
  {
    if (array_key_exists($argName, $this->ARGUMENTS)) {
      return $this->ARGUMENTS[$argName];
    } elseif (array_key_exists($argName, MemberLoginReminderTask::ARGUMENT_DEFAULTS)) {
      return self::ARGUMENT_DEFAULTS[$argName];
    }
    throw new LogicException('bad argument name: ' . $argName);
  }
}
