<?php

declare(strict_types=1);

namespace Quicko\Clubmanager\Hooks;

use Quicko\Clubmanager\Domain\Model\Member;
use Quicko\Clubmanager\Domain\Model\MemberJournalEntry;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

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
     * Speichert Entry-IDs, für die die Vergangenheits-Warnung im Request bereits ausgegeben wurde.
     * @var array<string|int, bool>
     */
    private static array $warnedPastEffectiveDateEntries = [];


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
                $this->translate('flash.activation_blocked.no_ident', 'Activation not possible: Member number (ident) is missing.'),
                $this->translate('flash.validation_error.title', 'Validation Error'),
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
        // Bestimme ob es ein bestehender Eintrag ist
        $existingUid = str_starts_with((string) $id, 'NEW') ? null : (int) $id;

        // CR8: Reaktivierung von hidden=1 auf hidden=0 blockieren, wenn neuere entry_date existieren
        if ($existingUid !== null) {
            if ($this->validateHiddenReactivation($fieldArray, $existingUid, $id)) {
                return;
            }
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

        $this->addPastEffectiveDateWarning($entryType, $effectiveDate, $id);

        // Member-UID ermitteln
        $memberUid = $this->resolveMemberUid($fieldArray, $id, $dataHandler);

        // Bug 7: Level-Change Validierung (old_level != new_level)
        if ($entryType === MemberJournalEntry::ENTRY_TYPE_LEVEL_CHANGE) {
            if ($this->validateLevelChange($fieldArray, $oldLevel, $newLevel, $id)) {
                return; // Eintrag wurde blockiert
            }

            // CR2: Prüfe auf offene Level-Changes (excludeUid = sich selbst bei bestehenden Einträgen)
            if ($memberUid !== null) {
                if ($this->validateNoPendingLevelChange($fieldArray, $memberUid, $existingUid, $id)) {
                    return; // Eintrag wurde blockiert
                }
            }
        }

        // Status-Change spezifische Validierungen
        if ($entryType === MemberJournalEntry::ENTRY_TYPE_STATUS_CHANGE) {
            if ($targetState !== null && $this->validateBillingManualCancelledNotAllowed($fieldArray, (int) $targetState, $id)) {
                return;
            }

            // CR7: Pending Kündigungs-Eintrag auf "aktiv" zurückstellen:
            // nicht als regulären Status-Change speichern, sondern als Rücknahme
            // behandeln (Entry wird verborgen).
            if ($existingUid !== null && $targetState !== null) {
                if ($this->applyPendingCancellationRevert($fieldArray, $existingUid, (int) $targetState)) {
                    return;
                }
            }

            // CR4: "Beantragt" darf in Base nicht manuell gesetzt werden
            if ($targetState !== null && $this->validateBeantragtNotAllowed($fieldArray, (int) $targetState, $id, $memberUid)) {
                return; // Eintrag wurde blockiert
            }

            // CR3/CR7: Gleicher Status blockieren (auch beim Edit eines pending Eintrags)
            if ($memberUid !== null && $targetState !== null) {
                if (
                    $this->shouldValidateNotSameStatus($existingUid, $fieldArray, (int) $targetState)
                    && $this->validateNotSameStatus($fieldArray, $memberUid, (int) $targetState, $id, $dataHandler)
                ) {
                    return; // Eintrag wurde blockiert
                }
            }

            // CR2: Prüfe auf offene Status-Changes (excludeUid = sich selbst bei bestehenden Einträgen)
            if ($memberUid !== null) {
                if ($this->validateNoPendingStatusChange($fieldArray, $memberUid, $existingUid, $id)) {
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
     * CR4: Validiert, dass der Status "beantragt" nicht manuell gesetzt werden kann.
     *
     * @return bool True wenn blockiert, false wenn OK
     */
    private function validateBeantragtNotAllowed(
        array &$fieldArray,
        int $targetState,
        string|int $id,
        ?int $memberUid
    ): bool {
        if ($targetState !== Member::STATE_APPLIED) {
            return false;
        }

        self::$invalidEntryIds[$id] = $memberUid ?? 0;
        if ($memberUid !== null) {
            self::$blockedMemberUids[$memberUid] = true;
        }

        $pid = $fieldArray['pid'] ?? null;
        $fieldArray = [];
        if ($pid !== null) {
            $fieldArray['pid'] = $pid;
        }

        $this->addFlashMessage(
            $this->translate(
                'flash.status_change_applied_not_allowed',
                'Status "beantragt" can only be created automatically by Pro registration and cannot be set manually.'
            ),
            $this->translate('flash.validation_error.title', 'Validation Error'),
            ContextualFeedbackSeverity::ERROR
        );

        return true;
    }

    /**
     * Bei aktivem Billing darf "gekündigt" nicht manuell angelegt werden.
     *
     * @return bool True wenn blockiert, false wenn OK
     */
    private function validateBillingManualCancelledNotAllowed(
        array &$fieldArray,
        int $targetState,
        string|int $id
    ): bool {
        if ($targetState !== Member::STATE_CANCELLED || !ExtensionManagementUtility::isLoaded('clubmanager_billing')) {
            return false;
        }

        $isNewEntry = str_starts_with((string) $id, 'NEW');
        // Nur manuelle Neuanlage blockieren, bestehende Einträge weiterhin bearbeitbar lassen.
        if (!$isNewEntry) {
            return false;
        }

        self::$invalidEntryIds[$id] = 0;
        $pid = $fieldArray['pid'] ?? null;
        $fieldArray = [];
        if ($pid !== null) {
            $fieldArray['pid'] = $pid;
        }

        $this->addFlashMessage(
            $this->translate(
                'flash.status_change_cancelled_not_allowed_with_billing',
                'Manual status change to "cancelled" is not allowed while Billing is active. Please use cancellation request.'
            ),
            $this->translate('flash.validation_error.title', 'Validation Error'),
            ContextualFeedbackSeverity::ERROR
        );

        return true;
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
                $this->translate('flash.level_change_same_level', 'Level change not possible: New level is the same as the current level.'),
                $this->translate('flash.validation_error.title', 'Validation Error'),
                ContextualFeedbackSeverity::ERROR
            );

            return true;
        }

        return false;
    }

    /**
     * CR7: Wenn ein bestehender pending "gekündigt"-Eintrag auf "aktiv" geändert wird,
     * wird der Eintrag als Rücknahme verborgen statt als neuer No-Op-Statuswechsel gespeichert.
     */
    private function applyPendingCancellationRevert(array &$fieldArray, int $existingUid, int $newTargetState): bool
    {
        if ($newTargetState !== Member::STATE_ACTIVE) {
            return false;
        }

        $record = BackendUtility::getRecord(
            self::TABLE_NAME,
            $existingUid,
            'entry_type,target_state,processed,hidden,pid'
        );
        if (!is_array($record)) {
            return false;
        }

        $isPendingStatusCancellation =
            ($record['entry_type'] ?? '') === MemberJournalEntry::ENTRY_TYPE_STATUS_CHANGE
            && (int) ($record['target_state'] ?? -1) === Member::STATE_CANCELLED
            && empty($record['processed'])
            && (int) ($record['hidden'] ?? 0) === 0;

        if (!$isPendingStatusCancellation) {
            return false;
        }

        $fieldArray = ['hidden' => 1];
        if (isset($record['pid'])) {
            $fieldArray['pid'] = (int) $record['pid'];
        }

        $this->addFlashMessage(
            $this->translate(
                'flash.pending_cancellation_reverted',
                'Pending cancellation was automatically reverted.'
            ),
            $this->translate('flash.validation_warning.title', 'Validation Warning'),
            ContextualFeedbackSeverity::WARNING
        );

        return true;
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

            $messageKey = 'flash.status_change_same_status';
            $messageFallback = 'Status change not possible: Target status is the same as the current status.';
            if (
                $targetState === Member::STATE_ACTIVE
                && $this->hasPendingFutureCancellationStatusChange($memberUid)
            ) {
                $messageKey = 'flash.status_change_same_status_with_pending_cancellation';
                $messageFallback = 'Status change not possible: A planned cancellation already exists. Please deactivate that entry (hidden=1) to revert the cancellation.';
            }

            $this->addFlashMessage(
                $this->translate($messageKey, $messageFallback),
                $this->translate('flash.validation_error.title', 'Validation Error'),
                ContextualFeedbackSeverity::ERROR
            );

            return true;
        }

        return false;
    }

    private function hasPendingFutureCancellationStatusChange(int $memberUid): bool
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable(self::TABLE_NAME);
        $queryBuilder->getRestrictions()->removeAll();

        $todayStart = (new \DateTime('today'))->getTimestamp();
        $count = (int) $queryBuilder
            ->count('uid')
            ->from(self::TABLE_NAME)
            ->where(
                $queryBuilder->expr()->eq('member', $queryBuilder->createNamedParameter($memberUid)),
                $queryBuilder->expr()->eq('entry_type', $queryBuilder->createNamedParameter(MemberJournalEntry::ENTRY_TYPE_STATUS_CHANGE)),
                $queryBuilder->expr()->eq('target_state', $queryBuilder->createNamedParameter(Member::STATE_CANCELLED)),
                $queryBuilder->expr()->gt('effective_date', $queryBuilder->createNamedParameter($todayStart)),
                $queryBuilder->expr()->or(
                    $queryBuilder->expr()->isNull('processed'),
                    $queryBuilder->expr()->eq('processed', $queryBuilder->createNamedParameter(0))
                ),
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0)),
                $queryBuilder->expr()->eq('hidden', $queryBuilder->createNamedParameter(0))
            )
            ->executeQuery()
            ->fetchOne();

        return $count > 0;
    }

    /**
     * CR3/CR7: Same-status-Validierung nur anwenden für:
     * - neue Status-Change-Einträge
     * - bestehende, unverarbeitete Status-Change-Einträge, deren target_state tatsächlich geändert wird
     */
    private function shouldValidateNotSameStatus(?int $existingUid, array $fieldArray, int $newTargetState): bool
    {
        if ($existingUid === null) {
            return true;
        }

        $record = BackendUtility::getRecord(
            self::TABLE_NAME,
            $existingUid,
            'entry_type,target_state,processed,hidden'
        );
        if (!is_array($record)) {
            return true;
        }

        if (($record['entry_type'] ?? '') !== MemberJournalEntry::ENTRY_TYPE_STATUS_CHANGE) {
            return false;
        }

        $isPending = empty($record['processed']) && (int) ($record['hidden'] ?? 0) === 0;
        if (!$isPending) {
            return false;
        }

        // CR7-Sonderfall: Bestehende pending Kündigung (target=cancelled) wird auf
        // active zurückgestellt. Dieser Flow wird nachgelagert automatisch als
        // "reverted" behandelt und darf nicht durch Same-Status blockiert werden.
        $oldTargetState = (int) ($record['target_state'] ?? -1);
        if ($oldTargetState === Member::STATE_CANCELLED && $newTargetState === Member::STATE_ACTIVE) {
            return false;
        }

        if (!array_key_exists('target_state', $fieldArray)) {
            return false;
        }

        return (int) ($record['target_state'] ?? -1) !== $newTargetState;
    }

    /**
     * CR2: Validiert dass kein offener Status-Change für diesen Member existiert.
     * Verwendet direkte DBAL-Queries statt Extbase Repository (Hook-Kontext).
     *
     * @return bool True wenn blockiert, false wenn OK
     */
    private function validateNoPendingStatusChange(
        array &$fieldArray,
        int $memberUid,
        ?int $excludeUid,
        string|int $id
    ): bool {
        $hasPending = $this->hasPendingEntry(
            $memberUid,
            MemberJournalEntry::ENTRY_TYPE_STATUS_CHANGE,
            $excludeUid
        );

        if ($hasPending) {
            self::$invalidEntryIds[$id] = $memberUid;

            $pid = $fieldArray['pid'] ?? null;
            $fieldArray = [];
            if ($pid !== null) {
                $fieldArray['pid'] = $pid;
            }

            $this->addFlashMessage(
                $this->translate('flash.pending_status_change_exists', 'A pending status change already exists for this member. Please wait until it is processed.'),
                $this->translate('flash.validation_error.title', 'Validation Error'),
                ContextualFeedbackSeverity::ERROR
            );

            return true;
        }

        return false;
    }

    /**
     * CR2: Validiert dass kein offener Level-Change für diesen Member existiert.
     * Verwendet direkte DBAL-Queries statt Extbase Repository (Hook-Kontext).
     *
     * @return bool True wenn blockiert, false wenn OK
     */
    private function validateNoPendingLevelChange(
        array &$fieldArray,
        int $memberUid,
        ?int $excludeUid,
        string|int $id
    ): bool {
        $hasPending = $this->hasPendingEntry(
            $memberUid,
            MemberJournalEntry::ENTRY_TYPE_LEVEL_CHANGE,
            $excludeUid
        );

        if ($hasPending) {
            self::$invalidEntryIds[$id] = $memberUid;

            $pid = $fieldArray['pid'] ?? null;
            $fieldArray = [];
            if ($pid !== null) {
                $fieldArray['pid'] = $pid;
            }

            $this->addFlashMessage(
                $this->translate('flash.pending_level_change_exists', 'A pending level change already exists for this member. Please wait until it is processed.'),
                $this->translate('flash.validation_error.title', 'Validation Error'),
                ContextualFeedbackSeverity::ERROR
            );

            return true;
        }

        return false;
    }

    /**
     * Prüft per DBAL ob ein offener Eintrag eines bestimmten Typs für einen Member existiert.
     */
    private function hasPendingEntry(int $memberUid, string $entryType, ?int $excludeUid = null): bool
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable(self::TABLE_NAME);

        // Entferne Standard-Restrictions, wir prüfen hidden/deleted explizit
        $queryBuilder->getRestrictions()->removeAll();

        $queryBuilder
            ->count('uid')
            ->from(self::TABLE_NAME)
            ->where(
                $queryBuilder->expr()->eq('member', $queryBuilder->createNamedParameter($memberUid)),
                $queryBuilder->expr()->eq('entry_type', $queryBuilder->createNamedParameter($entryType)),
                $queryBuilder->expr()->or(
                    $queryBuilder->expr()->isNull('processed'),
                    $queryBuilder->expr()->eq('processed', $queryBuilder->createNamedParameter(0))
                ),
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0)),
                $queryBuilder->expr()->eq('hidden', $queryBuilder->createNamedParameter(0))
            );

        if ($excludeUid !== null) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->neq('uid', $queryBuilder->createNamedParameter($excludeUid))
            );
        }

        $count = (int) $queryBuilder->executeQuery()->fetchOne();

        return $count > 0;
    }

    /**
     * CR8: Validiert, dass hidden=1 Einträge nicht reaktiviert werden, wenn neuere Einträge existieren.
     *
     * @return bool True wenn blockiert, false wenn OK
     */
    private function validateHiddenReactivation(array &$fieldArray, int $uid, string|int $id): bool
    {
        if (!array_key_exists('hidden', $fieldArray)) {
            return false;
        }
        if ((int) $fieldArray['hidden'] !== 0) {
            return false;
        }

        $record = BackendUtility::getRecord(self::TABLE_NAME, $uid, 'hidden,member,entry_date');
        if (!is_array($record)) {
            return false;
        }

        $wasHidden = (int) ($record['hidden'] ?? 0) === 1;
        if (!$wasHidden) {
            return false;
        }

        $memberUid = (int) ($record['member'] ?? 0);
        $entryDate = (int) ($record['entry_date'] ?? 0);
        if ($memberUid <= 0 || $entryDate <= 0) {
            return false;
        }

        if (!$this->hasNewerJournalEntryByEntryDate($memberUid, $entryDate, $uid)) {
            return false;
        }

        self::$invalidEntryIds[$id] = $memberUid;
        self::$blockedMemberUids[$memberUid] = true;

        $pid = $fieldArray['pid'] ?? null;
        $fieldArray = [];
        if ($pid !== null) {
            $fieldArray['pid'] = $pid;
        }

        $this->addFlashMessage(
            $this->translate(
                'flash.journal_reactivation_blocked_newer_entries',
                'Reactivation not possible: Newer journal entries exist.'
            ),
            $this->translate('flash.validation_error.title', 'Validation Error'),
            ContextualFeedbackSeverity::ERROR
        );

        return true;
    }

    private function hasNewerJournalEntryByEntryDate(int $memberUid, int $entryDate, int $excludeUid): bool
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable(self::TABLE_NAME);
        $queryBuilder->getRestrictions()->removeAll();

        $count = (int) $queryBuilder
            ->count('uid')
            ->from(self::TABLE_NAME)
            ->where(
                $queryBuilder->expr()->eq('member', $queryBuilder->createNamedParameter($memberUid)),
                $queryBuilder->expr()->gt('entry_date', $queryBuilder->createNamedParameter($entryDate)),
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0)),
                $queryBuilder->expr()->neq('uid', $queryBuilder->createNamedParameter($excludeUid))
            )
            ->executeQuery()
            ->fetchOne();

        return $count > 0;
    }

    /**
     * Holt den aktuellen Status eines Members aus DB oder Datamap.
     */
    private function getCurrentMemberState(int $memberUid, DataHandler $dataHandler): ?int
    {
        // Datamap-State nur verwenden, wenn der State im selben Request
        // explizit und tatsächlich vom DB-Wert abweichend geändert wurde.
        $stateFromDatamap = $this->getExplicitStateFromDatamap($memberUid, $dataHandler);
        if ($stateFromDatamap !== null) {
            return $stateFromDatamap;
        }

        // Aus DB holen
        $memberRecord = BackendUtility::getRecord(self::MEMBER_TABLE, $memberUid, 'state');
        if (is_array($memberRecord) && isset($memberRecord['state'])) {
            return (int) $memberRecord['state'];
        }

        return null;
    }

    /**
     * Liefert den State aus der Datamap nur dann, wenn es sich um eine
     * explizite und tatsächliche State-Änderung im aktuellen Request handelt.
     */
    private function getExplicitStateFromDatamap(int $memberUid, DataHandler $dataHandler): ?int
    {
        $memberData = $dataHandler->datamap[self::MEMBER_TABLE][$memberUid] ?? null;
        if (!is_array($memberData) || !array_key_exists('state', $memberData)) {
            return null;
        }

        $incomingState = (int) $memberData['state'];
        $memberRecord = BackendUtility::getRecord(self::MEMBER_TABLE, $memberUid, 'state');
        if (!is_array($memberRecord) || !isset($memberRecord['state'])) {
            return null;
        }

        $dbState = (int) $memberRecord['state'];
        return $incomingState !== $dbState ? $incomingState : null;
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
        // Prüfe effective_date - muss gesetzt sein
        $effectiveDate = $this->parseEffectiveDate($fieldArray['effective_date'] ?? null);
        if ($effectiveDate === null) {
            return;
        }

        if ($memberUid === null) {
            return;
        }

        // Prüfe ob Member ident hat - IMMER für Aktivierungs-Einträge, auch zukünftige!
        $memberRecord = BackendUtility::getRecord(self::MEMBER_TABLE, $memberUid, 'ident,email');
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
                $this->translate('flash.activation_blocked.no_ident', 'Activation not possible: Member number (ident) is missing.'),
                $this->translate('flash.validation_error.title', 'Validation Error'),
                ContextualFeedbackSeverity::ERROR
            );
            return;
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
        self::$warnedPastEffectiveDateEntries = [];
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
     * Ermittelt die Member-UID aus fieldArray, bestehendem Record oder Parent-Kontext (IRRE).
     *
     * Bei IRRE-Children steht die member-UID oft nicht im fieldArray, da sie
     * automatisch vom DataHandler zugewiesen wird. In diesem Fall ermitteln wir
     * die Member-UID aus dem Parent-Kontext.
     */
    private function resolveMemberUid(array $fieldArray, string|int $id, DataHandler $dataHandler): ?int
    {
        // 1. Aus fieldArray (direktes member-Feld)
        $memberUid = $fieldArray['member'] ?? null;
        if ($memberUid !== null && (int) $memberUid > 0) {
            return (int) $memberUid;
        }

        // 2. Aus bestehendem Record (bei Updates)
        if (is_numeric($id)) {
            $record = BackendUtility::getRecord(self::TABLE_NAME, (int) $id, 'member');
            if (is_array($record) && isset($record['member'])) {
                return (int) $record['member'];
            }
        }

        // 3. Aus Parent-Kontext (IRRE): Prüfe welcher Member gerade in der Datamap ist
        $memberDatamap = $dataHandler->datamap[self::MEMBER_TABLE] ?? [];
        foreach ($memberDatamap as $memberId => $memberData) {
            // Prüfe ob dieser Member journal_entries enthält, die auf unseren Eintrag verweisen
            $journalEntries = $memberData['journal_entries'] ?? null;
            if ($journalEntries !== null && is_string($journalEntries)) {
                // Format: "NEW12345,NEW12346,123,124" oder "NEW12345"
                $entryIds = explode(',', $journalEntries);
                if (in_array((string) $id, $entryIds, true)) {
                    // Gefunden! Dieser Member enthält unseren Journal-Eintrag
                    if (is_numeric($memberId)) {
                        return (int) $memberId;
                    }
                }
            }
        }

        // 4. Fallback: Wenn nur ein Member in der Datamap ist, nehmen wir diesen
        if (count($memberDatamap) === 1) {
            $memberId = array_key_first($memberDatamap);
            if (is_numeric($memberId)) {
                return (int) $memberId;
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

    private function addPastEffectiveDateWarning(mixed $entryType, \DateTime $effectiveDate, string|int $id): void
    {
        if (
            $entryType !== MemberJournalEntry::ENTRY_TYPE_STATUS_CHANGE
            && $entryType !== MemberJournalEntry::ENTRY_TYPE_LEVEL_CHANGE
        ) {
            return;
        }

        if (isset(self::$warnedPastEffectiveDateEntries[$id])) {
            return;
        }

        $today = new \DateTime('today');
        if ($effectiveDate >= $today) {
            return;
        }

        self::$warnedPastEffectiveDateEntries[$id] = true;

        $this->addFlashMessage(
            $this->translate(
                'flash.past_effective_date.warning',
                'The effective date is in the past. This change may affect billing/statistics and might require corrections.'
            ),
            $this->translate('flash.validation_warning.title', 'Validation Warning'),
            ContextualFeedbackSeverity::WARNING
        );
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

    /**
     * Übersetzt einen Label-Key mit dem Backend-LanguageService.
     * Verwendet die Sprache des aktuellen Backend-Users.
     */
    private function translate(string $key, string $fallback): string
    {
        $languageService = $GLOBALS['LANG'] ?? null;
        if ($languageService === null) {
            return $fallback;
        }

        $translated = $languageService->sL('LLL:EXT:clubmanager/Resources/Private/Language/locallang_be.xlf:' . $key);
        
        return $translated ?: $fallback;
    }
}
