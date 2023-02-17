<?php

namespace Quicko\Clubmanager\Hooks;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FeUserEmailHook
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

  public function processDatamap_afterAllOperations(\TYPO3\CMS\Core\DataHandling\DataHandler &$pObj)
  {
    if (!array_key_exists('tx_clubmanager_domain_model_member', $pObj->datamap)) {
      return;
    }
    foreach ($pObj->datamap['tx_clubmanager_domain_model_member'] as $uid => $propertyMap) {

      $feUserId = $propertyMap["feuser"];
      if (str_starts_with($feUserId, 'NEW')) {
        if (array_key_exists($feUserId, $pObj->substNEWwithIDs)) {
          $newFeUserUid = $pObj->substNEWwithIDs[$feUserId];
        } else {
          continue;
        }
      } else {
        $newFeUserUid = $feUserId;
      }

      $record = BackendUtility::getRecord(
        'fe_users',
        $newFeUserUid,
        '*',
      );
      if ($record) {
        $updatePasswordCommand = [];
        $updatePasswordCommand['fe_users'][$newFeUserUid]['email'] = $propertyMap['email'];
        $dataHandler = $this->getDataHandler();
        $dataHandler->start($updatePasswordCommand, []);
        $commandResult = $dataHandler->process_datamap();
        if ($commandResult === false) {
          $this->logger->error(
            sprintf(
              'Failed to process datamap for command on fe_user (%d)',
              $newFeUserUid,
              ['command' => $updatePasswordCommand]
            )
          );
        }
      }
    }
  }
}
