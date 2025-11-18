<?php

namespace Quicko\Clubmanager\Hooks;

use Quicko\Clubmanager\Service\MemberJournalService;
use Quicko\Clubmanager\Domain\Repository\MemberJournalEntryRepository;
use Quicko\Clubmanager\Domain\Repository\MemberRepository;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

class ProcessMemberJournalHook
{
  protected Logger $logger;

  public function __construct(?Logger $logger = null)
  {
    if ($logger === null) {
      $logManager = GeneralUtility::makeInstance(LogManager::class);
      $this->logger = $logManager->getLogger(__CLASS__);
    } else {
      $this->logger = $logger;
    }
  }

  public function processDatamap_afterDatabaseOperations(
    string &$status,
    string &$table,
    string &$id,
    array &$fieldArray,
    DataHandler &$pObj
  ): void {
    if ($table !== 'tx_clubmanager_domain_model_member') {
      return;
    }

    $uid = $id;
    if ($status === 'new') {
      $uid = $pObj->substNEWwithIDs[$id] ?? null;
    }

    if (!$uid) {
      return;
    }

    try {
      $journalService = GeneralUtility::makeInstance(
        MemberJournalService::class,
        GeneralUtility::makeInstance(MemberJournalEntryRepository::class),
        GeneralUtility::makeInstance(MemberRepository::class),
        GeneralUtility::makeInstance(PersistenceManager::class)
      );

      $processedCount = $journalService->processPendingEntriesForMember((int) $uid);

      if ($processedCount > 0) {
        $this->logger->info(
          sprintf('Processed %d pending journal entries for member %d', $processedCount, $uid)
        );
      }
    } catch (\Exception $e) {
      $this->logger->error(
        sprintf('Error processing journal entries for member %d: %s', $uid, $e->getMessage())
      );
    }
  }
}

