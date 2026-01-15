<?php

namespace Quicko\Clubmanager\Hooks;

use Quicko\Clubmanager\Domain\Model\Member;
use Quicko\Clubmanager\Domain\Model\MemberJournalEntry;
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

  /**
   * Hook nach Abschluss ALLER DataHandler-Operationen.
   * Wird einmal am Ende aufgerufen, nachdem alle Records (inkl. IRRE-Children) gespeichert sind.
   *
   * - Verarbeitet fällige Journal-Einträge sofort (nutzt MemberJournalService)
   * - Prüft Konsistenz zwischen Member und Journal-Historie
   *
   * Das Command (clubmanager:journal:process) dient zusätzlich für Batch-Verarbeitung via Cron.
   */
  public function processDatamap_afterAllOperations(DataHandler &$pObj): void
  {
    // Sammle alle betroffenen Member-UIDs
    $memberUids = [];

    // Direkt gespeicherte Member
    if (isset($pObj->datamap['tx_clubmanager_domain_model_member'])) {
      foreach ($pObj->datamap['tx_clubmanager_domain_model_member'] as $id => $data) {
        $uid = is_numeric($id) ? (int) $id : ($pObj->substNEWwithIDs[$id] ?? null);
        if ($uid) {
          $memberUids[$uid] = true;
        }
      }
    }

    // Member über Journal-Einträge (IRRE-Children)
    if (isset($pObj->datamap['tx_clubmanager_domain_model_memberjournalentry'])) {
      foreach ($pObj->datamap['tx_clubmanager_domain_model_memberjournalentry'] as $id => $data) {
        $memberUid = $data['member'] ?? null;
        if ($memberUid && (int) $memberUid > 0) {
          $memberUids[(int) $memberUid] = true;
        }
      }
    }

    // Verarbeite alle betroffenen Member
    foreach (array_keys($memberUids) as $memberUid) {
      $this->processMemberSave($memberUid);
    }
  }

  /**
   * Verarbeitet fällige Journal-Einträge und prüft Konsistenz für einen Member
   */
  protected function processMemberSave(int $memberUid): void
  {
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
      $processedCount = $journalService->processPendingEntriesForMember($memberUid);

      if ($processedCount > 0) {
        $this->logger->info(
          sprintf('Processed %d pending journal entries for member %d', $processedCount, $memberUid)
        );
      }

      // 2. Prüfe Konsistenz zwischen Member und Journal-Historie
      $corrected = $this->ensureMemberConsistencyWithJournal(
        $memberUid,
        $journalRepository,
        $memberRepository,
        $persistenceManager
      );

      if ($corrected) {
        $this->logger->info(
          sprintf('Corrected member %d state to match journal history', $memberUid)
        );
      }
    } catch (\Exception $e) {
      $this->logger->error(
        sprintf('Error processing journal for member %d: %s', $memberUid, $e->getMessage())
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

