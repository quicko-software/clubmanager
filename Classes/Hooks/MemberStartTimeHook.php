<?php

namespace Quicko\Clubmanager\Hooks;

use Quicko\Clubmanager\Domain\Model\Member;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class MemberStartTimeHook
{
  private function getDataHandler(): DataHandler
  {
    return GeneralUtility::makeInstance(DataHandler::class);
  }

  /**
   * @param string      $status
   * @param string      $table
   * @param string         $id
   * @param array       $fieldArray
   * @param DataHandler $pObj
   */
  public function processDatamap_afterDatabaseOperations(string &$status, string &$table, string &$id, array &$fieldArray, DataHandler &$pObj): void
  {
    if ($table !== 'tx_clubmanager_domain_model_member') {
      return;
    }
    if ($status !== 'update' && $status !== 'new') {
      return;
    }

    $uid = $id;
    if ($status === 'new') {
      $uid = $pObj->substNEWwithIDs[$id];
    }

    $record = BackendUtility::getRecord(
      'tx_clubmanager_domain_model_member',
      $uid,
      '*',
    );
    $starttime = $record['starttime'];
    $state = $record['state'];

    if ($state == Member::STATE_ACTIVE && ($starttime == null || $starttime == 0)) {
      $updateCommand = [];
      $updateCommand['tx_clubmanager_domain_model_member'][$uid]['starttime'] = time();
      $dataHandler = $this->getDataHandler();
      $dataHandler->start($updateCommand, []);
      $dataHandler->process_datamap();
    }
  }
}
