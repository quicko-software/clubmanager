<?php

declare(strict_types=1);

namespace Quicko\Clubmanager\Hooks;

use Doctrine\DBAL\ParameterType;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Prevents editing of system-created, member-created, and processed journal entries.
 * This is a server-side protection that ensures these entries cannot be modified.
 * Admins can always edit.
 */
class PreventSystemJournalEntryEditHook
{
    private const TABLE_NAME = 'tx_clubmanager_domain_model_memberjournalentry';
    private const CREATOR_TYPE_SYSTEM = 0;
    private const CREATOR_TYPE_MEMBER = 2;

    /**
     * Hook called before data is processed by DataHandler.
     * Removes system journal entries from the incoming data to prevent modifications.
     */
    public function processDatamap_preProcessFieldArray(
        array &$fieldArray,
        string $table,
        string|int $id,
        DataHandler $dataHandler
    ): void {
        if ($table !== self::TABLE_NAME) {
            return;
        }

        // Admins can always edit
        if ($this->getBackendUser()->isAdmin()) {
            return;
        }

        // Only check existing records (not new ones)
        if (!is_numeric($id)) {
            return;
        }

        // Check if this entry should be protected
        $record = $this->getRecord((int) $id);
        if ($record === null) {
            return;
        }

        $creatorType = (int) ($record['creator_type'] ?? -1);
        $processed = (int) ($record['processed'] ?? 0);
        $isProcessed = $processed > 0;

        // Prevent editing if:
        // - Entry was created by system/member, OR
        // - Entry has been processed
        $shouldPreventEdit = ($creatorType === self::CREATOR_TYPE_SYSTEM ||
            $creatorType === self::CREATOR_TYPE_MEMBER ||
            $isProcessed);

        if (!$shouldPreventEdit) {
            return;
        }

        // Prevent modification by clearing the field array
        $fieldArray = [];

        // Add flash message to inform user
        if ($isProcessed) {
            $message = 'Verarbeitete Journal-Einträge können nicht mehr bearbeitet werden.';
        } elseif ($creatorType === self::CREATOR_TYPE_SYSTEM) {
            $message = 'System-Journaleinträge können nicht bearbeitet werden.';
        } else {
            $message = 'Mitglieder-Journaleinträge können nicht bearbeitet werden.';
        }

        $this->addFlashMessage(
            $message,
            'Bearbeitung nicht möglich',
            ContextualFeedbackSeverity::WARNING
        );
    }

    private function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }

    private function getRecord(int $uid): ?array
    {
        $queryBuilder = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)
            ->getQueryBuilderForTable(self::TABLE_NAME);

        $queryBuilder->getRestrictions()->removeAll();

        $record = $queryBuilder
            ->select('creator_type', 'processed')
            ->from(self::TABLE_NAME)
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid, ParameterType::INTEGER))
            )
            ->executeQuery()
            ->fetchAssociative();

        return $record ?: null;
    }

    private function addFlashMessage(string $message, string $title, ContextualFeedbackSeverity $severity): void
    {
        $flashMessage = GeneralUtility::makeInstance(
            FlashMessage::class,
            $message,
            $title,
            $severity,
            true
        );

        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        $flashMessageService->getMessageQueueByIdentifier()->enqueue($flashMessage);
    }
}

