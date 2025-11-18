<?php

namespace Quicko\Clubmanager\Hooks;

use Quicko\Clubmanager\Domain\Model\Member;
use Quicko\Clubmanager\Domain\Model\MemberJournalEntry;
use Quicko\Clubmanager\Service\MemberJournalService;
use Quicko\Clubmanager\Domain\Repository\MemberJournalEntryRepository;
use Quicko\Clubmanager\Domain\Repository\MemberRepository;
use TYPO3\CMS\Backend\Utility\BackendUtility;
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

  /**
   * Hook beim Speichern:
   * - Member: Verarbeitet fällige Journal-Einträge und prüft Konsistenz
   * - Journal-Eintrag: Verarbeitet ihn sofort wenn fällig
   */
  public function processDatamap_afterDatabaseOperations(
    string &$status,
    string &$table,
    string &$id,
    array &$fieldArray,
    DataHandler &$pObj
  ): void {
    if ($table === 'tx_clubmanager_domain_model_member') {
      $this->processMemberSave($status, $id, $pObj);
    } elseif ($table === 'tx_clubmanager_domain_model_memberjournalentry') {
      $this->processJournalEntrySave($status, $id, $pObj);
    }
  }

  /**
   * Verarbeitet das Speichern eines Members
   */
  protected function processMemberSave(string $status, string $id, DataHandler $pObj): void
  {
    $uid = $id;
    if ($status === 'new') {
      $uid = $pObj->substNEWwithIDs[$id] ?? null;
    }

    if (!$uid) {
      return;
    }

    try {
      $journalRepository = GeneralUtility::makeInstance(MemberJournalEntryRepository::class);
      $memberRepository = GeneralUtility::makeInstance(MemberRepository::class);
      $persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);

      $journalService = GeneralUtility::makeInstance(
        MemberJournalService::class,
        $journalRepository,
        $memberRepository,
        $persistenceManager
      );

      // 1. Verarbeite fällige Journal-Einträge
      $processedCount = $journalService->processPendingEntriesForMember((int) $uid);

      if ($processedCount > 0) {
        $this->logger->info(
          sprintf('Processed %d pending journal entries for member %d', $processedCount, $uid)
        );
      }

      // 2. Prüfe Konsistenz zwischen Member und Journal-Historie
      $corrected = $this->ensureMemberConsistencyWithJournal(
        (int) $uid,
        $journalRepository,
        $memberRepository,
        $persistenceManager
      );

      if ($corrected) {
        $this->logger->info(
          sprintf('Corrected member %d state to match journal history', $uid)
        );
      }
    } catch (\Exception $e) {
      $this->logger->error(
        sprintf('Error processing journal for member %d: %s', $uid, $e->getMessage())
      );
    }
  }

  /**
   * Verarbeitet das Speichern eines Journal-Eintrags
   * Verarbeitet ihn sofort wenn das effective_date bereits erreicht ist
   */
  protected function processJournalEntrySave(string $status, string $id, DataHandler $pObj): void
  {
    $uid = $id;
    if ($status === 'new') {
      $uid = $pObj->substNEWwithIDs[$id] ?? null;
    }

    if (!$uid) {
      return;
    }

    try {
      // Lade den Record direkt aus der DB (nicht über Extbase-Repository)
      $record = BackendUtility::getRecord('tx_clubmanager_domain_model_memberjournalentry', (int) $uid);

      if (!$record) {
        return;
      }

      // Nur Status- und Level-Änderungen verarbeiten
      $entryType = $record['entry_type'] ?? '';
      if (
        $entryType !== MemberJournalEntry::ENTRY_TYPE_STATUS_CHANGE
        && $entryType !== MemberJournalEntry::ENTRY_TYPE_LEVEL_CHANGE
      ) {
        return;
      }

      // Bereits verarbeitet?
      if (!empty($record['processed'])) {
        return;
      }

      // Ist das effective_date bereits erreicht?
      $effectiveDate = $record['effective_date'] ?? 0;
      $now = time();
      if ($effectiveDate === 0 || $effectiveDate > $now) {
        return;
      }

      // Member-UID
      $memberUid = $record['member'] ?? 0;
      if ($memberUid === 0) {
        return;
      }

      // Verarbeite den Eintrag sofort für den zugehörigen Member
      $journalRepository = GeneralUtility::makeInstance(MemberJournalEntryRepository::class);
      $memberRepository = GeneralUtility::makeInstance(MemberRepository::class);
      $persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);

      $journalService = GeneralUtility::makeInstance(
        MemberJournalService::class,
        $journalRepository,
        $memberRepository,
        $persistenceManager
      );

      $journalService->processPendingEntriesForMember($memberUid);
    } catch (\Exception $e) {
      $this->logger->error(
        sprintf('Error processing journal entry %d: %s', $uid, $e->getMessage())
      );
    }
  }

  /**
   * Stellt sicher, dass der Member-Zustand mit der Journal-Historie übereinstimmt
   * Korrigiert automatisch wenn nötig (z.B. nach Löschen von Journal-Einträgen)
   */
  protected function ensureMemberConsistencyWithJournal(
    int $memberUid,
    MemberJournalEntryRepository $journalRepository,
    MemberRepository $memberRepository,
    PersistenceManager $persistenceManager
  ): bool {
    $member = $memberRepository->findByUidWithoutStoragePage($memberUid);

    if (!$member instanceof Member) {
      return false;
    }

    $corrected = false;

    // Prüfe Status-Konsistenz
    $lastStatusEntry = $journalRepository->findLastProcessedEntry(
      $memberUid,
      MemberJournalEntry::ENTRY_TYPE_STATUS_CHANGE
    );

    if ($lastStatusEntry !== null) {
      $expectedState = $lastStatusEntry->getTargetState();
      if ($expectedState !== null && $member->getState() !== $expectedState) {
        $member->setState($expectedState);
        $corrected = true;

        // Korrigiere auch Zeitfelder
        $effectiveDate = $lastStatusEntry->getEffectiveDate();
        if ($effectiveDate !== null) {
          switch ($expectedState) {
            case Member::STATE_ACTIVE:
              if (!$member->getStarttime()) {
                $member->setStarttime($effectiveDate);
              }
              $member->setEndtime(null);
              break;

            case Member::STATE_CANCELLED:
            case Member::STATE_SUSPENDED:
              $member->setEndtime($effectiveDate);
              break;
          }
        }
      }
    }

    // Prüfe Level-Konsistenz
    $lastLevelEntry = $journalRepository->findLastProcessedEntry(
      $memberUid,
      MemberJournalEntry::ENTRY_TYPE_LEVEL_CHANGE
    );

    if ($lastLevelEntry !== null) {
      $expectedLevel = $lastLevelEntry->getNewLevel();
      if ($expectedLevel !== null && $member->getLevel() !== $expectedLevel) {
        $member->setLevel($expectedLevel);
        $corrected = true;
      }
    }

    if ($corrected) {
      $memberRepository->update($member);
      $persistenceManager->persistAll();
    }

    return $corrected;
  }
}

