<?php

namespace Quicko\Clubmanager\Hooks;

use Quicko\Clubmanager\Domain\Model\Member;
use Quicko\Clubmanager\Domain\Model\MemberJournalEntry;
use Quicko\Clubmanager\Service\MemberJournalService;
use Quicko\Clubmanager\Domain\Repository\MemberJournalEntryRepository;
use Quicko\Clubmanager\Domain\Repository\MemberRepository;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

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
    $activeStatusMemberUids = [];

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
        $entryType = $data['entry_type'] ?? null;
        $targetState = $data['target_state'] ?? null;
        $isProcessed = false;

        $resolvedEntryUid = is_numeric($id) ? (int) $id : ($pObj->substNEWwithIDs[$id] ?? null);

        // Für existierende Einträge: Prüfe processed-Status aus DB
        if ($resolvedEntryUid && is_numeric($id)) {
          $record = BackendUtility::getRecord(
            'tx_clubmanager_domain_model_memberjournalentry',
            $resolvedEntryUid,
            'member,entry_type,target_state,processed'
          );
          if (is_array($record)) {
            $memberUid = $memberUid ?? ($record['member'] ?? null);
            $entryType = $entryType ?? ($record['entry_type'] ?? null);
            $targetState = $targetState ?? ($record['target_state'] ?? null);
            $isProcessed = !empty($record['processed']);
          }
        } elseif ($resolvedEntryUid && ($memberUid === null || $entryType === null || $targetState === null)) {
          // Neuer Eintrag: Fehlende Daten aus DB holen
          $record = BackendUtility::getRecord(
            'tx_clubmanager_domain_model_memberjournalentry',
            $resolvedEntryUid,
            'member,entry_type,target_state,processed'
          );
          if (is_array($record)) {
            $memberUid = $memberUid ?? ($record['member'] ?? null);
            $entryType = $entryType ?? ($record['entry_type'] ?? null);
            $targetState = $targetState ?? ($record['target_state'] ?? null);
            $isProcessed = !empty($record['processed']);
          }
        }

        if ($memberUid && (int) $memberUid > 0) {
          $memberUid = (int) $memberUid;
          $memberUids[$memberUid] = true;

          // Nur NEUE (nicht-processed) Einträge für autoResolveCancellation berücksichtigen
          if (
            !$isProcessed
            && $entryType === MemberJournalEntry::ENTRY_TYPE_STATUS_CHANGE
            && (int) $targetState === Member::STATE_ACTIVE
          ) {
            $activeStatusMemberUids[$memberUid] = true;
          }
        }
      }
    }

    // Verarbeite alle betroffenen Member
    foreach (array_keys($memberUids) as $memberUid) {
      $autoResolveCancellation = isset($activeStatusMemberUids[$memberUid]);
      $this->processMemberSave($memberUid, $autoResolveCancellation);
    }
  }

  /**
   * Verarbeitet fällige Journal-Einträge und prüft Konsistenz für einen Member
   */
  protected function processMemberSave(int $memberUid, bool $autoResolveCancellation = false): void
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

      if ($autoResolveCancellation) {
        $journalService->resolvePendingCancellationForMember(
          $memberUid,
          LocalizationUtility::translate('memberjournal.cancellation_reverted', 'clubmanager')
            ?? 'Cancellation request reverted by status change to active'
        );
      }

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

      // 3. Ohne Billing: Endtime bei geplanter Kündigung sofort setzen
      if (!$this->isBillingInstalled()) {
        $this->applyPendingCancellationEndtime(
          $memberUid,
          $journalRepository,
          $memberRepository,
          $persistenceManager
        );
      }

      // CR6: Bei Aktivierung ohne E-Mail eine Warning-Flashmessage anzeigen.
      if ($autoResolveCancellation) {
        $this->addActivationNoEmailWarning($memberUid);
      }

      // 4. Synchronisiere FE-User disable-Status mit Member-Status
      $this->syncFeUserDisableState($memberUid);

      if ($corrected) {
        $this->logger->info(
          sprintf('Corrected member %d state to match journal history', $memberUid)
        );
      }
    } catch (\InvalidArgumentException $e) {
      // Save ist zu diesem Zeitpunkt bereits gelaufen;
      // dies ist eine nachgelagerte Verarbeitungswarnung.
      $this->addFlashMessage(
        $e->getMessage(),
        $this->translate('flash.journal_processing_warning.title', 'Journal processing warning'),
        ContextualFeedbackSeverity::WARNING
      );
      $this->logger->warning(
        sprintf('Validation error for member %d: %s', $memberUid, $e->getMessage())
      );
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
      if ($expectedState !== null) {
        if ($member->getState() !== $expectedState) {
          $member->setState($expectedState);
          $corrected = true;
        }

        // Korrigiere auch Zeitfelder
        $effectiveDate = $lastStatusEntry->getEffectiveDate();
        if ($effectiveDate !== null) {
          switch ($expectedState) {
            case Member::STATE_ACTIVE:
              if (!$member->getStarttime()) {
                $member->setStarttime($effectiveDate);
                $corrected = true;
              }
              // Endtime nur leeren, wenn keine geplante Kündigung existiert
              $pendingCancellation = $journalRepository->findPendingCancellationStatusChange($memberUid);
              if ($pendingCancellation === null && $member->getEndtime() !== null) {
                $member->setEndtime(null);
                $corrected = true;
              }
              break;

            case Member::STATE_CANCELLED:
              $currentEndtime = $member->getEndtime();
              if (
                $currentEndtime === null
                || $currentEndtime->getTimestamp() !== $effectiveDate->getTimestamp()
              ) {
                $member->setEndtime($effectiveDate);
                $corrected = true;
              }
              break;

            case Member::STATE_SUSPENDED:
              // Ruhend setzt KEINE endtime - Member bleibt Mitglied
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

  private function applyPendingCancellationEndtime(
    int $memberUid,
    MemberJournalEntryRepository $journalRepository,
    MemberRepository $memberRepository,
    PersistenceManager $persistenceManager
  ): void {
    $pendingCancellation = $journalRepository->findPendingCancellationStatusChange($memberUid);
    if (!$pendingCancellation instanceof MemberJournalEntry) {
      return;
    }

    $effectiveDate = $pendingCancellation->getEffectiveDate();
    if ($effectiveDate === null) {
      return;
    }

    $now = new \DateTime('now');
    if ($effectiveDate <= $now) {
      return;
    }

    $member = $memberRepository->findByUidWithoutStoragePage($memberUid);
    if (!$member instanceof Member) {
      return;
    }

    $currentEndtime = $member->getEndtime();
    if ($currentEndtime && $currentEndtime->getTimestamp() === $effectiveDate->getTimestamp()) {
      return;
    }

    $member->setEndtime($effectiveDate);
    $memberRepository->update($member);
    $persistenceManager->persistAll();
  }

  private function isBillingInstalled(): bool
  {
    return class_exists(\Quicko\ClubmanagerBilling\Service\CancellationPeriodCalculator::class);
  }

  /**
   * Synchronisiert den FE-User disable-Status mit dem Member-Status.
   * Deaktiviert FE-User wenn Member nicht mehr ACTIVE ist,
   * aktiviert FE-User wenn Member wieder ACTIVE wird.
   */
  private function syncFeUserDisableState(int $memberUid): void
  {
    // Hole aktuellen Member-Status
    $memberRecord = BackendUtility::getRecord('tx_clubmanager_domain_model_member', $memberUid, 'state');
    if (!is_array($memberRecord)) {
      return;
    }

    $currentState = (int) ($memberRecord['state'] ?? 0);
    $shouldDisable = ($currentState !== Member::STATE_ACTIVE);

    $connection = GeneralUtility::makeInstance(ConnectionPool::class)
      ->getConnectionForTable('fe_users');

    // Aktualisiere alle FE-User dieses Members
    $affectedRows = $connection->update(
      'fe_users',
      ['disable' => $shouldDisable ? 1 : 0],
      [
        'clubmanager_member' => $memberUid,
        'deleted' => 0,
      ]
    );

    if ($affectedRows > 0) {
      $this->logger->info(
        sprintf(
          'Updated fe_users disable=%d for member %d (%d rows)',
          $shouldDisable ? 1 : 0,
          $memberUid,
          $affectedRows
        )
      );
    }
  }

  private function addActivationNoEmailWarning(int $memberUid): void
  {
    $memberRecord = BackendUtility::getRecord('tx_clubmanager_domain_model_member', $memberUid, 'state,email');
    if (!is_array($memberRecord)) {
      return;
    }

    if ((int) ($memberRecord['state'] ?? 0) !== Member::STATE_ACTIVE) {
      return;
    }

    $email = trim((string) ($memberRecord['email'] ?? ''));
    if ($email !== '') {
      return;
    }

    $this->addFlashMessage(
      LocalizationUtility::translate('flash.activation_warning.no_email', 'clubmanager')
        ?? 'No email address is set. Activation can continue, but automatic login communication is not possible.',
      LocalizationUtility::translate('flash.validation_warning.title', 'clubmanager')
        ?? 'Validation Warning',
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

