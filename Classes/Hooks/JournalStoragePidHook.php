<?php

declare(strict_types=1);

namespace Quicko\Clubmanager\Hooks;

use Quicko\Clubmanager\Utils\SettingUtils;
use TYPO3\CMS\Core\DataHandling\DataHandler;

/**
 * Overrides the PID for new MemberJournalEntry records
 * with the configured memberJournalStoragePid from Site Settings.
 *
 * Must run BEFORE AutoFillJournalEntryFieldsHook (registration order matters).
 */
final class JournalStoragePidHook
{
    private const TABLE_NAME = 'tx_clubmanager_domain_model_memberjournalentry';

    public function processDatamap_preProcessFieldArray(
        array &$fieldArray,
        string $table,
        string|int $id,
        DataHandler $dataHandler
    ): void {
        if ($table !== self::TABLE_NAME) {
            return;
        }

        if (!str_starts_with((string) $id, 'NEW')) {
            return;
        }

        $currentPid = (int) ($fieldArray['pid'] ?? 0);
        if ($currentPid <= 0) {
            return;
        }

        $storagePid = (int) SettingUtils::getSiteSetting(
            $currentPid,
            'clubmanager.memberJournalStoragePid',
            0
        );

        if ($storagePid > 0) {
            $fieldArray['pid'] = $storagePid;
        }
    }
}
