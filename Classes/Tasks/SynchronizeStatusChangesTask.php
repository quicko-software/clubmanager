<?php

namespace Quicko\Clubmanager\Tasks;

use Quicko\Clubmanager\Service\MemberStatusSynchronizationService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

class SynchronizeStatusChangesTask extends AbstractTask
{
    public function execute(): bool
    {
        try {
            $statusSyncService = GeneralUtility::makeInstance(
                MemberStatusSynchronizationService::class,
                GeneralUtility::makeInstance(\Quicko\Clubmanager\Domain\Repository\MemberStatusChangeRepository::class),
                GeneralUtility::makeInstance(\Quicko\Clubmanager\Domain\Repository\MemberRepository::class),
                GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager::class)
            );

            $statusSyncService->synchronizePendingChanges();

            return true;
        } catch (\Exception $e) {
            // Error wird vom Scheduler automatisch geloggt
            return false;
        }
    }

    /**
     * This method returns additional information about the task.
     *
     * @return string
     */
    public function getAdditionalInformation(): string
    {
        return 'Synchronisiert fällige Member-Status-Änderungen in die Member-Datensätze.';
    }
}

