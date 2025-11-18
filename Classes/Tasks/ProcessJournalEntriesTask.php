<?php

namespace Quicko\Clubmanager\Tasks;

use Quicko\Clubmanager\Service\MemberJournalService;
use Quicko\Clubmanager\Domain\Repository\MemberJournalEntryRepository;
use Quicko\Clubmanager\Domain\Repository\MemberRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

class ProcessJournalEntriesTask extends AbstractTask
{
  public function execute(): bool
  {
    try {
      $journalService = GeneralUtility::makeInstance(
        MemberJournalService::class,
        GeneralUtility::makeInstance(MemberJournalEntryRepository::class),
        GeneralUtility::makeInstance(MemberRepository::class),
        GeneralUtility::makeInstance(PersistenceManager::class)
      );

      $journalService->processPendingEntries();

      return true;
    } catch (\Exception $e) {
      return false;
    }
  }

  public function getAdditionalInformation(): string
  {
    return 'Verarbeitet fällige Journal-Einträge (Status- und Level-Änderungen).';
  }
}


