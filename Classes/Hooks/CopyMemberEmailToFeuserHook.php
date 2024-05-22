<?php

namespace Quicko\Clubmanager\Hooks;

use Quicko\Clubmanager\Utils\HookUtils;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class CopyMemberEmailToFeuserHook
{
  /**
   * @var Logger
   */
  protected $logger;

  /**
   * @param Logger|null $logger
   */
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

  /**
   * @return DataHandler
   */
  private function getDataHandler()
  {
    return GeneralUtility::makeInstance(DataHandler::class);
  }

  public function processDatamap_afterAllOperations(DataHandler &$pObj)
  {
    if (!array_key_exists('tx_clubmanager_domain_model_member', $pObj->datamap)) {
      return;
    }

    foreach ($pObj->datamap['tx_clubmanager_domain_model_member'] as $uid => $memberProps) {
      $feuserUid = HookUtils::getRecordUid($memberProps['feuser'], $pObj);
      if (! $feuserUid) {
        continue;
      }
      $feuserRecord = BackendUtility::getRecord('fe_users', $feuserUid, '*');
      if (! $feuserRecord) {
        continue;
      }
      $hasMemberEmailChanged = ($memberProps['email'] !== $feuserRecord['email']);
      if (! $hasMemberEmailChanged) {
        continue;
      }
      $cmd = [];
      $cmd['fe_users'][$feuserUid]['email'] = $memberProps['email'];
      $dataHandler = $this->getDataHandler();
      $dataHandler->start($cmd, []);
      $commandResult = $dataHandler->process_datamap();
      if ($commandResult === false) {
        HookUtils::logError($this->logger, 'fe_users', $feuserUid);
      }
    }
  }
}
