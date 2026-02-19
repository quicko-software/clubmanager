<?php

declare(strict_types=1);

namespace Quicko\Clubmanager\Hooks;

use Quicko\Clubmanager\Domain\Model\MemberJournalEntry;
use Quicko\Clubmanager\Domain\Repository\MemberJournalEntryRepository;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Automatically fills fields when creating new journal entries:
 * - entry_date: Set to current timestamp
 * - creator_type: Set to "Backend" (1)
 * - old_level: Set from member's current level or last processed level_change entry
 *
 * Note: Required field validation for effective_date is handled via TCA columnsOverrides.
 */
final class AutoFillJournalEntryFieldsHook
{
    private const TABLE_NAME = 'tx_clubmanager_domain_model_memberjournalentry';
    private const CREATOR_TYPE_BACKEND = 1;

    /**
     * Hook called before data is processed by DataHandler.
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

        // Only for new records
        if (!str_starts_with((string) $id, 'NEW')) {
            return;
        }

        // Set entry_date to current timestamp (required since field is readOnly)
        $fieldArray['entry_date'] = time();

        // Set creator_type to Backend (1) - this ensures it's always set correctly
        $fieldArray['creator_type'] = self::CREATOR_TYPE_BACKEND;

        // Handle level_change specific fields
        $entryType = $fieldArray['entry_type'] ?? '';
        if ($entryType === MemberJournalEntry::ENTRY_TYPE_LEVEL_CHANGE) {
            $this->fillOldLevel($fieldArray, $id, $dataHandler);
            $this->ensureNewLevelIsSet($fieldArray);
        }
    }

    /**
     * Ensure new_level is set for level_change entries.
     * If not provided, copy from old_level to prevent NULL values.
     */
    private function ensureNewLevelIsSet(array &$fieldArray): void
    {
        // Check if new_level is set (null, empty string, or not set at all)
        if (!isset($fieldArray['new_level']) || $fieldArray['new_level'] === '' || $fieldArray['new_level'] === null) {
            // Use old_level as fallback (no change)
            $fieldArray['new_level'] = $fieldArray['old_level'] ?? 0;
        }
    }

    /**
     * Fill old_level from member's current level or last processed level_change entry.
     */
    private function fillOldLevel(array &$fieldArray, string|int $id, DataHandler $dataHandler): void
    {
        // Skip if old_level is already set to a non-zero value
        if (isset($fieldArray['old_level']) && (int) $fieldArray['old_level'] !== 0) {
            return;
        }

        // Get member UID - try multiple sources
        $memberUid = $this->getMemberUid($fieldArray, $id, $dataHandler);
        if ($memberUid === 0) {
            return;
        }

        // Try to get old_level from last processed level_change entry
        $journalRepository = GeneralUtility::makeInstance(MemberJournalEntryRepository::class);
        $lastLevelEntry = $journalRepository->findLastProcessedEntry(
            $memberUid,
            MemberJournalEntry::ENTRY_TYPE_LEVEL_CHANGE
        );

        if ($lastLevelEntry !== null) {
            $fieldArray['old_level'] = $lastLevelEntry->getNewLevel();
            return;
        }

        // Fall back to current member level
        $memberRecord = BackendUtility::getRecord('tx_clubmanager_domain_model_member', $memberUid);
        if ($memberRecord) {
            $fieldArray['old_level'] = (int) ($memberRecord['level'] ?? 0);
        }
    }

    /**
     * Get member UID from various sources (direct field, IRRE parent context).
     *
     * For IRRE inline records, the parent UID is available through:
     * 1. Direct 'member' field value (if set by DataHandler)
     * 2. DataHandler's datamap (parent record contains child reference in journal_entries field)
     */
    private function getMemberUid(array $fieldArray, string|int $id, DataHandler $dataHandler): int
    {
        // 1. Try direct field value (foreign_field 'member')
        // This is set by DataHandler for IRRE children
        if (isset($fieldArray['member']) && (int) $fieldArray['member'] > 0) {
            return (int) $fieldArray['member'];
        }

        // 2. Check datamap for parent member records that reference this child
        // When IRRE child is created, the parent's journal_entries field contains the NEW id
        foreach ($dataHandler->datamap as $tableName => $records) {
            if ($tableName !== 'tx_clubmanager_domain_model_member') {
                continue;
            }
            foreach ($records as $recordUid => $recordData) {
                $journalEntries = $recordData['journal_entries'] ?? '';
                if ($journalEntries === '') {
                    continue;
                }

                // Check if this member's journal_entries contains our new record ID
                if (str_contains((string) $journalEntries, (string) $id)) {
                    // Numeric UID = existing member
                    if (is_numeric($recordUid) && (int) $recordUid > 0) {
                        return (int) $recordUid;
                    }
                    // NEW UID = new member being created simultaneously
                    // Can't determine level yet, return 0 (will use default)
                    return 0;
                }
            }
        }

        return 0;
    }
}
