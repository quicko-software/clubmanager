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
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

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

        // Prüfe ob echte Änderungen vorliegen (nicht nur System-Felder durch IRRE-Handling)
        $ignoredFields = ['tstamp', 'crdate', 'pid', 'sorting', 'l10n_diffsource'];
        $meaningfulChanges = array_diff_key($fieldArray, array_flip($ignoredFields));
        if (empty($meaningfulChanges)) {
            return;
        }

        // Check if this entry should be protected
        $record = $this->getRecordForComparison((int) $id);
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

        // Bug 6 Fix: Deep Compare - Prüfe ob tatsächlich inhaltliche Änderungen vorliegen
        // IRRE sendet beim Speichern des Parents auch unveränderte Children-Daten
        if (!$this->hasActualChanges($meaningfulChanges, $record)) {
            return; // Keine echten Änderungen -> Hook abbrechen, keine Warnung
        }

        // Prevent modification by clearing the field array
        $fieldArray = [];

        // Add flash message to inform user
        if ($isProcessed) {
            $message = LocalizationUtility::translate('flash.journal_edit_blocked.processed', 'clubmanager')
                ?? 'Processed journal entries cannot be edited.';
        } elseif ($creatorType === self::CREATOR_TYPE_SYSTEM) {
            $message = LocalizationUtility::translate('flash.journal_edit_blocked.system_entry', 'clubmanager')
                ?? 'System-generated journal entries cannot be edited.';
        } else {
            $message = LocalizationUtility::translate('flash.journal_edit_blocked.member_entry', 'clubmanager')
                ?? 'Member-created journal entries cannot be edited.';
        }

        $this->addFlashMessage(
            $message,
            LocalizationUtility::translate('flash.journal_edit_blocked.title', 'clubmanager')
                ?? 'Editing not possible',
            ContextualFeedbackSeverity::WARNING
        );
    }

    private function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }

    /**
     * Lädt den Datensatz mit allen für den Vergleich relevanten Feldern.
     */
    private function getRecordForComparison(int $uid): ?array
    {
        $queryBuilder = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)
            ->getQueryBuilderForTable(self::TABLE_NAME);

        $queryBuilder->getRestrictions()->removeAll();

        $record = $queryBuilder
            ->select('*')
            ->from(self::TABLE_NAME)
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid, ParameterType::INTEGER))
            )
            ->executeQuery()
            ->fetchAssociative();

        return $record ?: null;
    }

    /**
     * Prüft ob tatsächliche inhaltliche Änderungen vorliegen.
     * Vergleicht die eingehenden Werte mit den DB-Werten.
     *
     * @param array<string, mixed> $incomingData Die zu speichernden Daten
     * @param array<string, mixed> $dbRecord Der aktuelle Datensatz aus der DB
     */
    private function hasActualChanges(array $incomingData, array $dbRecord): bool
    {
        foreach ($incomingData as $field => $newValue) {
            // Feld existiert nicht im DB-Record -> neue Daten
            if (!array_key_exists($field, $dbRecord)) {
                return true;
            }

            $dbValue = $dbRecord[$field];

            // Normalisierung für Vergleich (beide zu String, trim)
            $normalizedNew = $this->normalizeValue($newValue);
            $normalizedDb = $this->normalizeValue($dbValue);

            if ($normalizedNew !== $normalizedDb) {
                return true;
            }
        }

        return false;
    }

    /**
     * Normalisiert einen Wert für den Vergleich.
     * Konvertiert zu String und trimmt Whitespace.
     */
    private function normalizeValue(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        // Boolean zu "0" oder "1"
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return trim((string) $value);
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

