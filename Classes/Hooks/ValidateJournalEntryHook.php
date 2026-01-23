<?php

declare(strict_types=1);

namespace Quicko\Clubmanager\Hooks;

use Quicko\Clubmanager\Domain\Model\Member;
use Quicko\Clubmanager\Domain\Model\MemberJournalEntry;
use Quicko\Clubmanager\Domain\Repository\MemberJournalEntryRepository;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Validates journal entries BEFORE saving.
 *
 * Prevents creating status-change entries to STATE_ACTIVE if:
 * - The member has no ident (Mitgliedsnummer)
 * - The effective_date is in the past (would trigger immediate processing)
 *
 * IMPORTANT: This hook must run BEFORE other journal hooks to block invalid entries early.
 */
class ValidateJournalEntryHook
{
    private const TABLE_NAME = 'tx_clubmanager_domain_model_memberjournalentry';
    private const MEMBER_TABLE = 'tx_clubmanager_domain_model_member';

    /**
     * Speichert IDs von ungültigen Journal-Einträgen, die nach dem Speichern gelöscht werden müssen.
     * @var array<string|int, int>
     */
    private static array $invalidEntryIds = [];

    /**
     * Speichert Member-UIDs, deren Speicherung blockiert werden soll.
     * @var array<int, bool>
     */
    private static array $blockedMemberUids = [];

    /**
     * Hook called before data is processed by DataHandler.
     * Blocks journal entries that would cause invalid state transitions.
     *
     * WICHTIG: DataHandler verarbeitet Parent (Member) VOR Children (Journal).
     * Daher prüfen wir beim Member bereits die Datamap auf ungültige Journal-Einträge.
     */
    public function processDatamap_preProcessFieldArray(
        array &$fieldArray,
        string $table,
        string|int $id,
        DataHandler $dataHandler
    ): void {
        // Bei Member-Speicherung: Prüfe vorab die Datamap auf ungültige Journal-Einträge
        if ($table === self::MEMBER_TABLE) {
            $this->validateMemberWithJournalEntries($fieldArray, $id, $dataHandler);
            return;
        }

        if ($table !== self::TABLE_NAME) {
            return;
        }

        // Journal-Eintrag: Prüfe ob bereits als ungültig markiert
        if (isset(self::$invalidEntryIds[$id])) {
            // Bereits als ungültig markiert, leere fieldArray
            // WICHTIG: pid muss erhalten bleiben für DataHandler bei neuen Records
            $pid = $fieldArray['pid'] ?? null;
            $fieldArray = [];
            if ($pid !== null) {
                $fieldArray['pid'] = $pid;
            }
            return;
        }

        // Validiere einzelnen Journal-Eintrag (falls nicht über Member validiert)
        $this->validateSingleJournalEntry($fieldArray, $id, $dataHandler);
    }

    /**
     * Validiert einen Member und seine Journal-Einträge aus der Datamap.
     */
    private function validateMemberWithJournalEntries(
        array &$fieldArray,
        string|int $memberId,
        DataHandler $dataHandler
    ): void {
        $memberUid = is_numeric($memberId) ? (int) $memberId : null;

        // Prüfe ob Member bereits blockiert ist
        if ($memberUid !== null && isset(self::$blockedMemberUids[$memberUid])) {
            // WICHTIG: pid muss erhalten bleiben für DataHandler bei neuen Records
            $pid = $fieldArray['pid'] ?? null;
            $fieldArray = [];
            if ($pid !== null) {
                $fieldArray['pid'] = $pid;
            }
            return;
        }

        // Hole aktuelle ident - aus fieldArray oder DB
        $ident = $fieldArray['ident'] ?? null;
        if ($ident === null && $memberUid !== null) {
            $memberRecord = BackendUtility::getRecord(self::MEMBER_TABLE, $memberUid, 'ident');
            $ident = $memberRecord['ident'] ?? '';
        }

        // Wenn ident vorhanden ist, keine weitere Prüfung nötig
        if ($ident !== null && trim((string) $ident) !== '') {
            return;
        }

        // Kein ident - prüfe ob ungültige Journal-Einträge in Datamap sind
        // WICHTIG: Nur NEUE Einträge (NEW prefix) prüfen, nicht bestehende!
        $journalData = $dataHandler->datamap[self::TABLE_NAME] ?? [];
        $now = new \DateTime('today');
        $hasInvalidEntry = false;

        foreach ($journalData as $entryId => $entryData) {
            // KRITISCH: Nur NEUE Einträge validieren!
            if (!str_starts_with((string) $entryId, 'NEW')) {
                continue;
            }

            // Prüfe ob dieser Eintrag zu diesem Member gehört
            $entryMemberUid = $entryData['member'] ?? null;

            // Bei IRRE-Children ist member oft nicht gesetzt, da es automatisch zugewiesen wird
            // Wir müssen annehmen, dass es zu diesem Member gehört wenn keine member-UID angegeben ist
            if ($entryMemberUid !== null && (int) $entryMemberUid !== $memberUid) {
                continue;
            }

            $entryType = $entryData['entry_type'] ?? null;
            $targetState = $entryData['target_state'] ?? null;

            // Nur Status-Wechsel auf ACTIVE prüfen
            if ($entryType !== MemberJournalEntry::ENTRY_TYPE_STATUS_CHANGE) {
                continue;
            }
            if ((int) $targetState !== Member::STATE_ACTIVE) {
                continue;
            }

            // Prüfe effective_date
            $effectiveDate = $this->parseEffectiveDate($entryData['effective_date'] ?? null);
            if ($effectiveDate === null || $effectiveDate > $now) {
                continue; // Zukunfts-Datum ist OK
            }

            // Ungültiger Eintrag gefunden!
            $hasInvalidEntry = true;
            self::$invalidEntryIds[$entryId] = $memberUid ?? 0;
        }

        if ($hasInvalidEntry) {
            // Blockiere Member-Speicherung
            if ($memberUid !== null) {
                self::$blockedMemberUids[$memberUid] = true;
            }
            
            // WICHTIG: pid muss erhalten bleiben für DataHandler bei neuen Records
            $pid = $fieldArray['pid'] ?? null;
            $fieldArray = [];
            if ($pid !== null) {
                $fieldArray['pid'] = $pid;
            }

            $this->addFlashMessage(
                LocalizationUtility::translate('flash.activation_blocked.no_ident', 'clubmanager')
                    ?? 'Activation not possible: Member number (ident) is missing.',
                LocalizationUtility::translate('flash.validation_error.title', 'clubmanager')
                    ?? 'Validation Error',
                ContextualFeedbackSeverity::ERROR
            );
        }
    }

    /**
     * Validiert einen einzelnen Journal-Eintrag (Fallback für direkte Journal-Bearbeitung).
     *
     * WICHTIG: Validierungen für Bug 7, CR2, CR3 gelten nur für NEUE Einträge (NEW prefix).
     * Bestehende Einträge (numerische IDs) werden nicht validiert, da sie bereits gespeichert
     * wurden und bei IRRE-Speicherung des Parents erneut übermittelt werden.
     *
     * WICHTIG: Validierung erfolgt erst wenn effective_date gesetzt ist!
     * Beim Type-Switching speichert FormEngine automatisch, bevor der User die Felder
     * ausfüllen kann. Daher darf die Validierung erst greifen, wenn der Eintrag
     * "vollständig" ist (= effective_date vorhanden).
     */
    private function validateSingleJournalEntry(
        array &$fieldArray,
        string|int $id,
        DataHandler $dataHandler
    ): void {
        // KRITISCH: Nur NEUE Einträge validieren!
        // Bestehende Einträge (numerische IDs) werden bei IRRE-Speicherung des Parents
        // erneut übermittelt, dürfen aber nicht erneut validiert/blockiert werden.
        $isNewEntry = str_starts_with((string) $id, 'NEW');
        if (!$isNewEntry) {
            return;
        }

        $entryType = $fieldArray['entry_type'] ?? null;
        $targetState = $fieldArray['target_state'] ?? null;
        $newLevel = $fieldArray['new_level'] ?? null;
        $oldLevel = $fieldArray['old_level'] ?? null;
        $effectiveDate = $this->parseEffectiveDate($fieldArray['effective_date'] ?? null);

        // KRITISCH: Validierung nur wenn effective_date gesetzt ist!
        // Beim Type-Switching speichert FormEngine automatisch mit Default-Werten.
        // Der User hatte noch keine Chance, die Felder korrekt auszufüllen.
        // Ohne effective_date ist der Eintrag sowieso nicht verarbeitbar.
        if ($effectiveDate === null) {
            return;
        }

        // Member-UID ermitteln
        $memberUid = $this->resolveMemberUid($fieldArray, $id, $dataHandler);

        // Bug 7: Level-Change Validierung (old_level != new_level)
        if ($entryType === MemberJournalEntry::ENTRY_TYPE_LEVEL_CHANGE) {
            if ($this->validateLevelChange($fieldArray, $oldLevel, $newLevel, $id)) {
                return; // Eintrag wurde blockiert
            }

            // CR2: Prüfe auf offene Level-Changes
            if ($memberUid !== null) {
                if ($this->validateNoPendingLevelChange($fieldArray, $memberUid, null, $id)) {
                    return; // Eintrag wurde blockiert
                }
            }
        }

        // Status-Change spezifische Validierungen
        if ($entryType === MemberJournalEntry::ENTRY_TYPE_STATUS_CHANGE) {
            // CR3: Gleicher Status blockieren
            if ($memberUid !== null && $targetState !== null) {
                if ($this->validateNotSameStatus($fieldArray, $memberUid, (int) $targetState, $id, $dataHandler)) {
                    return; // Eintrag wurde blockiert
                }
            }

            // CR2: Prüfe auf offene Status-Changes
            if ($memberUid !== null) {
                if ($this->validateNoPendingStatusChange($fieldArray, $memberUid, null, $id)) {
                    return; // Eintrag wurde blockiert
                }
            }

            // Bestehende Validierung: Aktivierung ohne ident blockieren
            if ((int) $targetState === Member::STATE_ACTIVE) {
                $this->validateActivationWithIdent($fieldArray, $id, $memberUid, $dataHandler);
            }
        }
    }

    /**
     * Bug 7: Validiert dass bei Level-Changes old_level != new_level ist.
     *
     * @return bool True wenn blockiert, false wenn OK
     */
    private function validateLevelChange(
        array &$fieldArray,
        mixed $oldLevel,
        mixed $newLevel,
        string|int $id
    ): bool {
        // Beide Werte müssen vorhanden sein
        if ($oldLevel === null || $newLevel === null) {
            return false;
        }

        $oldLevelInt = (int) $oldLevel;
        $newLevelInt = (int) $newLevel;

        if ($oldLevelInt === $newLevelInt) {
            self::$invalidEntryIds[$id] = 0;

            $pid = $fieldArray['pid'] ?? null;
            $fieldArray = [];
            if ($pid !== null) {
                $fieldArray['pid'] = $pid;
            }

            $this->addFlashMessage(
                LocalizationUtility::translate('flash.level_change_same_level', 'clubmanager')
                    ?? 'Level change not possible: New level is the same as the current level.',
                LocalizationUtility::translate('flash.validation_error.title', 'clubmanager')
                    ?? 'Validation Error',
                ContextualFeedbackSeverity::ERROR
            );

            return true;
        }

        return false;
    }

    /**
     * CR3: Validiert dass der Ziel-Status nicht dem aktuellen Status entspricht.
     *
     * @return bool True wenn blockiert, false wenn OK
     */
    private function validateNotSameStatus(
        array &$fieldArray,
        int $memberUid,
        int $targetState,
        string|int $id,
        DataHandler $dataHandler
    ): bool {
        // Aktuellen Status aus DB oder Datamap holen
        $currentState = $this->getCurrentMemberState($memberUid, $dataHandler);
        if ($currentState === null) {
            return false;
        }

        if ($currentState === $targetState) {
            self::$invalidEntryIds[$id] = $memberUid;

            $pid = $fieldArray['pid'] ?? null;
            $fieldArray = [];
            if ($pid !== null) {
                $fieldArray['pid'] = $pid;
            }

            $this->addFlashMessage(
                LocalizationUtility::translate('flash.status_change_same_status', 'clubmanager')
                    ?? 'Status change not possible: Target status is the same as the current status.',
                LocalizationUtility::translate('flash.validation_error.title', 'clubmanager')
                    ?? 'Validation Error',
                ContextualFeedbackSeverity::ERROR
            );

            return true;
        }

        return false;
    }

    /**
     * CR2: Validiert dass kein offener Status-Change für diesen Member existiert.
     *
     * @return bool True wenn blockiert, false wenn OK
     */
    private function validateNoPendingStatusChange(
        array &$fieldArray,
        int $memberUid,
        ?int $excludeUid,
        string|int $id
    ): bool {
        $repository = GeneralUtility::makeInstance(MemberJournalEntryRepository::class);
        $pendingEntry = $repository->findPendingStatusChange($memberUid, $excludeUid);

        if ($pendingEntry !== null) {
            self::$invalidEntryIds[$id] = $memberUid;

            $pid = $fieldArray['pid'] ?? null;
            $fieldArray = [];
            if ($pid !== null) {
                $fieldArray['pid'] = $pid;
            }

            $this->addFlashMessage(
                LocalizationUtility::translate('flash.pending_status_change_exists', 'clubmanager')
                    ?? 'A pending status change already exists for this member. Please wait until it is processed.',
                LocalizationUtility::translate('flash.validation_error.title', 'clubmanager')
                    ?? 'Validation Error',
                ContextualFeedbackSeverity::ERROR
            );

            return true;
        }

        return false;
    }

    /**
     * CR2: Validiert dass kein offener Level-Change für diesen Member existiert.
     *
     * @return bool True wenn blockiert, false wenn OK
     */
    private function validateNoPendingLevelChange(
        array &$fieldArray,
        int $memberUid,
        ?int $excludeUid,
        string|int $id
    ): bool {
        $repository = GeneralUtility::makeInstance(MemberJournalEntryRepository::class);
        $pendingEntry = $repository->findPendingLevelChange($memberUid, $excludeUid);

        if ($pendingEntry !== null) {
            self::$invalidEntryIds[$id] = $memberUid;

            $pid = $fieldArray['pid'] ?? null;
            $fieldArray = [];
            if ($pid !== null) {
                $fieldArray['pid'] = $pid;
            }

            $this->addFlashMessage(
                LocalizationUtility::translate('flash.pending_level_change_exists', 'clubmanager')
                    ?? 'A pending level change already exists for this member. Please wait until it is processed.',
                LocalizationUtility::translate('flash.validation_error.title', 'clubmanager')
                    ?? 'Validation Error',
                ContextualFeedbackSeverity::ERROR
            );

            return true;
        }

        return false;
    }

    /**
     * Holt den aktuellen Status eines Members aus DB oder Datamap.
     */
    private function getCurrentMemberState(int $memberUid, DataHandler $dataHandler): ?int
    {
        // Prüfe erst Datamap (falls im selben Request geändert)
        $memberData = $dataHandler->datamap[self::MEMBER_TABLE][$memberUid] ?? null;
        if (is_array($memberData) && isset($memberData['state'])) {
            return (int) $memberData['state'];
        }

        // Aus DB holen
        $memberRecord = BackendUtility::getRecord(self::MEMBER_TABLE, $memberUid, 'state');
        if (is_array($memberRecord) && isset($memberRecord['state'])) {
            return (int) $memberRecord['state'];
        }

        return null;
    }

    /**
     * Bestehende Validierung: Aktivierung ohne ident blockieren.
     */
    private function validateActivationWithIdent(
        array &$fieldArray,
        string|int $id,
        ?int $memberUid,
        DataHandler $dataHandler
    ): void {
        // Prüfe effective_date - nur blockieren wenn in Vergangenheit oder heute
        $effectiveDate = $this->parseEffectiveDate($fieldArray['effective_date'] ?? null);
        if ($effectiveDate === null) {
            return;
        }

        $now = new \DateTime('today');
        if ($effectiveDate > $now) {
            return;
        }

        if ($memberUid === null) {
            return;
        }

        // Prüfe ob Member ident hat
        $memberRecord = BackendUtility::getRecord(self::MEMBER_TABLE, $memberUid, 'ident');
        if (!is_array($memberRecord)) {
            return;
        }

        // Prüfe auch ob ident gerade im selben Request gesetzt wird
        $identFromDatamap = $this->getIdentFromDatamap($memberUid, $dataHandler);
        $ident = $identFromDatamap ?? ($memberRecord['ident'] ?? '');

        if (trim((string) $ident) === '') {
            self::$invalidEntryIds[$id] = $memberUid;
            self::$blockedMemberUids[$memberUid] = true;

            $pid = $fieldArray['pid'] ?? null;
            $fieldArray = [];
            if ($pid !== null) {
                $fieldArray['pid'] = $pid;
            }

            $this->addFlashMessage(
                LocalizationUtility::translate('flash.activation_blocked.no_ident', 'clubmanager')
                    ?? 'Activation not possible: Member number (ident) is missing.',
                LocalizationUtility::translate('flash.validation_error.title', 'clubmanager')
                    ?? 'Validation Error',
                ContextualFeedbackSeverity::ERROR
            );
        }
    }

    /**
     * Hook nach allen Operationen: Löscht ungültige Journal-Einträge die durch IRRE erstellt wurden.
     *
     * WICHTIG: Nur NEU erstellte Einträge (NEW prefix) werden gelöscht!
     * Bestehende Einträge (numerische IDs) dürfen NIEMALS gelöscht werden.
     */
    public function processDatamap_afterAllOperations(DataHandler &$dataHandler): void
    {
        if (empty(self::$invalidEntryIds)) {
            return;
        }

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable(self::TABLE_NAME);

        foreach (self::$invalidEntryIds as $id => $memberUid) {
            // KRITISCH: Nur NEW-Einträge löschen, NIEMALS bestehende!
            // $id ist der ursprüngliche Key (z.B. "NEW12345" oder numerisch)
            if (is_numeric($id)) {
                // Bestehender Eintrag - NICHT löschen!
                continue;
            }

            // NEW-Eintrag: Resolve zu tatsächlicher UID
            $resolvedUid = $dataHandler->substNEWwithIDs[$id] ?? null;

            if ($resolvedUid !== null && $resolvedUid > 0) {
                // Lösche den ungültigen neuen Eintrag
                $connection->delete(self::TABLE_NAME, ['uid' => (int) $resolvedUid]);
            }
        }

        // Reset für nächsten Request
        self::$invalidEntryIds = [];
        self::$blockedMemberUids = [];
    }

    /**
     * Parst das effective_date aus verschiedenen Formaten
     */
    private function parseEffectiveDate(mixed $value): ?\DateTime
    {
        if ($value === null || $value === '' || $value === 0) {
            return null;
        }

        if ($value instanceof \DateTime) {
            return $value;
        }

        if (is_numeric($value)) {
            return new \DateTime('@' . (int) $value);
        }

        if (is_string($value)) {
            try {
                return new \DateTime($value);
            } catch (\Exception) {
                return null;
            }
        }

        return null;
    }

    /**
     * Ermittelt die Member-UID aus fieldArray oder bestehendem Record
     */
    private function resolveMemberUid(array $fieldArray, string|int $id, DataHandler $dataHandler): ?int
    {
        // Aus fieldArray
        $memberUid = $fieldArray['member'] ?? null;
        if ($memberUid !== null && (int) $memberUid > 0) {
            return (int) $memberUid;
        }

        // Aus bestehendem Record
        if (is_numeric($id)) {
            $record = BackendUtility::getRecord(self::TABLE_NAME, (int) $id, 'member');
            if (is_array($record) && isset($record['member'])) {
                return (int) $record['member'];
            }
        }

        return null;
    }

    /**
     * Prüft ob ident im selben Request gesetzt wird
     */
    private function getIdentFromDatamap(int $memberUid, DataHandler $dataHandler): ?string
    {
        $memberData = $dataHandler->datamap['tx_clubmanager_domain_model_member'][$memberUid] ?? null;
        if (is_array($memberData) && isset($memberData['ident'])) {
            return (string) $memberData['ident'];
        }
        return null;
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
