<?php

namespace Quicko\Clubmanager\Hooks;

use Quicko\Clubmanager\Domain\Model\Mail\Task;
use Quicko\Clubmanager\FormEngine\PasswordReset;
use Quicko\Clubmanager\Mail\Generator\Arguments\PasswordRecoveryArguments;
use Quicko\Clubmanager\Mail\Generator\PasswordRecoveryGenerator;
use Quicko\Clubmanager\Mail\MailQueue;
use Quicko\Clubmanager\Utils\HookUtils;
use Quicko\Clubmanager\Utils\PasswordGenerator;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ResetFeuserPasswordHook
{
  /**
   * @var Logger
   */
  protected $logger;

  public function __construct(Logger $logger = null)
  {
    if ($logger === null) {
      /** @var LogManager $logManager */
      $logManager = GeneralUtility::makeInstance(LogManager::class);
      $this->logger = $logManager->getLogger(__CLASS__);
    } else {
      $this->logger = $logger;
    }
  }

  private function getDataHandler(): DataHandler
  {
    return GeneralUtility::makeInstance(DataHandler::class);
  }

  private function runUpdatePasswordCommand(string $feuserUid, string $newPassword): void
  {
    $hashInstance = GeneralUtility::makeInstance(PasswordHashFactory::class)->getDefaultHashInstance('FE');
    $hashedPassword = $hashInstance->getHashedPassword($newPassword);

    $updatePasswordCommand = [];
    $updatePasswordCommand['fe_users'][$feuserUid]['password'] = $hashedPassword;
    $dataHandler = $this->getDataHandler();
    $dataHandler->start($updatePasswordCommand, []);
    $commandResult = $dataHandler->process_datamap();
    if ($commandResult === false) {
      HookUtils::logError($this->logger, 'fe_users', $feuserUid);
    }
  }

  private function pushRecoveryMail(int $memberUid): void
  {
    /** @var PasswordRecoveryArguments $args */
    $args = new PasswordRecoveryArguments();
    $args->memberUid = $memberUid;
    $args->templateName = 'Logindata';
    MailQueue::addMailTask(PasswordRecoveryGenerator::class, $args, Task::PRIORITY_LEVEL_MEDIUM);
  }

  private function wasPasswordCleared(array $feuserProps): bool
  {
    if (array_key_exists('password', $feuserProps)) {
      $givenPassword = $feuserProps['password'];
      if ($givenPassword === '') {
        return true;
      }
    }

    return false;
  }

  private function userHasNoPasswordYet(array $feuserRecord): bool
  {
    return $feuserRecord['password'] === '';
  }

  private function userResetTokenIsSet(array $feuserRecord): bool
  {
    return in_array('password', $feuserRecord) ? $feuserRecord['password'] === PasswordReset::RESET_VALUE_TOKEN : false;
  }

  public function processDatamap_afterAllOperations(DataHandler &$pObj): void
  {
    if (!array_key_exists('fe_users', $pObj->datamap)) {
      return;
    }
    foreach ($pObj->datamap['fe_users'] as $uid => $feuserProps) {
      $feuserUid = HookUtils::getRecordUid(strval($uid), $pObj);
      if (!$feuserUid) {
        continue;
      }
      $feuserRecord = BackendUtility::getRecord('fe_users', $feuserUid, '*');
      if (!$feuserRecord) {
        continue;
      }

      $needToReset = $this->wasPasswordCleared($feuserProps) || $this->userHasNoPasswordYet($feuserRecord) || $this->userResetTokenIsSet($feuserProps);
      if (!$needToReset) {
        continue;
      }

      $newPassword = PasswordGenerator::generatePassword();
      $this->runUpdatePasswordCommand($feuserUid, $newPassword);
      $this->pushRecoveryMail($feuserRecord['clubmanager_member']);
    }
  }
}
