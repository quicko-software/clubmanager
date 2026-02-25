<?php

namespace Quicko\Clubmanager\Hooks;

use Quicko\Clubmanager\Domain\Model\Member;
use Quicko\Clubmanager\Domain\Model\MemberJournalEntry;
use Quicko\Clubmanager\Service\MemberJournalProjectionService;
use Quicko\Clubmanager\Service\MemberJournalService;
use Quicko\Clubmanager\Domain\Repository\MemberJournalEntryRepository;
use Quicko\Clubmanager\Domain\Repository\MemberRepository;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
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
  /**
   * Member-UIDs aus Cmdmap-Delete-Operationen, die nach Abschluss
   * der Command-Verarbeitung nachsynchronisiert werden müssen.
   * Static, damit der Zustand auch bei mehrfachen Hook-Instanzen im selben Request erhalten bleibt.
   *
   * @var array<int, bool>
   */
  protected static array $cmdmapDeleteMemberUids = [];

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

        // Stabilisiert IRRE-Hidden-Toggle beim ersten Save:
        // Wenn für einen pending Eintrag hidden=1 angefordert wurde,
        // den Zustand ggf. explizit persistieren.
        $this->enforcePendingHiddenOnFirstSave($resolvedEntryUid, $data);

        // Loeschen via Datamap (z.B. IRRE) kann als Feld "delete" kommen.
        // In diesem Fall muessen wir den betroffenen Member explizit aufloesen,
        // damit die Konsistenzpruefung nach dem ersten Speichern laeuft.
        if ((int) ($data['delete'] ?? 0) === 1 && $resolvedEntryUid) {
          $deletedEntryMemberUid = $this->findMemberUidForJournalEntry((int) $resolvedEntryUid);
          if ($deletedEntryMemberUid !== null) {
            $memberUids[$deletedEntryMemberUid] = true;
          }
          continue;
        }

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

    // Fallback: Einige IRRE-Requests transportieren hidden-Updates nicht
    // konsistent in die Datamap. Daher zusaetzlich die uebermittelten
    // Formdaten pruefen und pending hidden=1 sicher persistieren.
    $this->enforcePendingHiddenFromRequestData($memberUids);

    // Member über Journal-Delete-Commands (IRRE-Delete / direktes Löschen)
    if (isset($pObj->cmdmap['tx_clubmanager_domain_model_memberjournalentry'])) {
      foreach ($pObj->cmdmap['tx_clubmanager_domain_model_memberjournalentry'] as $id => $commands) {
        if (!is_array($commands) || !isset($commands['delete']) || !is_numeric($id)) {
          continue;
        }

        $memberUid = $this->findMemberUidForJournalEntry((int) $id);
        if ($memberUid !== null) {
          $memberUids[$memberUid] = true;
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
   * Erfasst Delete-Operationen aus process_cmdmap.
   * Dieser Hook wird im Delete-Lifecycle mit dem Original-Record aufgerufen.
   */
  public function processCmdmap_deleteAction(
    string $table,
    int $id,
    array $record,
    mixed &$recordWasDeleted,
    DataHandler $dataHandler
  ): void {
    if ($table !== 'tx_clubmanager_domain_model_memberjournalentry') {
      return;
    }

    $memberUid = (int) ($record['member'] ?? 0);
    if ($memberUid > 0) {
      self::$cmdmapDeleteMemberUids[$memberUid] = true;
    }
  }

  /**
   * Führt nach Abschluss aller Cmdmap-Operationen die Konsistenzprüfung
   * für betroffene Member aus.
   */
  public function processCmdmap_afterFinish(DataHandler $dataHandler): void
  {
    $memberUids = self::$cmdmapDeleteMemberUids;

    // Defensive Fallback: In einigen IRRE/Delete-Lifecycles wird processCmdmap_deleteAction
    // nicht zuverlässig für jeden Datensatz genutzt. Daher die Cmdmap hier erneut auswerten.
    $cmdmap = $dataHandler->cmdmap['tx_clubmanager_domain_model_memberjournalentry'] ?? null;
    if (is_array($cmdmap)) {
      foreach ($cmdmap as $id => $commands) {
        if (!is_array($commands) || !isset($commands['delete']) || !is_numeric((string) $id)) {
          continue;
        }

        $memberUid = $this->findMemberUidForJournalEntry((int) $id);
        if ($memberUid !== null) {
          $memberUids[$memberUid] = true;
        }
      }
    }

    if ($memberUids === []) {
      self::$cmdmapDeleteMemberUids = [];
      return;
    }

    foreach (array_keys($memberUids) as $memberUid) {
      $this->processMemberSave((int) $memberUid, false);
    }

    self::$cmdmapDeleteMemberUids = [];
  }

  private function findMemberUidForJournalEntry(int $journalEntryUid): ?int
  {
    $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
      ->getQueryBuilderForTable('tx_clubmanager_domain_model_memberjournalentry');
    $queryBuilder->getRestrictions()->removeByType(DeletedRestriction::class);

    $memberUid = $queryBuilder
      ->select('member')
      ->from('tx_clubmanager_domain_model_memberjournalentry')
      ->where(
        $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($journalEntryUid))
      )
      ->executeQuery()
      ->fetchOne();

    if ($memberUid === false || (int) $memberUid <= 0) {
      return null;
    }

    return (int) $memberUid;
  }

  /**
   * In seltenen IRRE-Faellen kann hidden=1 beim ersten Speichern eines Eintrags
   * nicht sauber persistiert werden. Für pending Einträge erzwingen wir deshalb
   * den gewünschten hidden=1-Zustand explizit nach Abschluss der Datamap-Verarbeitung.
   *
   * @param array<string, mixed> $journalData
   */
  private function enforcePendingHiddenOnFirstSave(?int $journalEntryUid, array $journalData): void
  {
    if ($journalEntryUid === null || $journalEntryUid <= 0) {
      return;
    }

    if (!array_key_exists('hidden', $journalData) || (int) $journalData['hidden'] !== 1) {
      return;
    }

    $record = BackendUtility::getRecord(
      'tx_clubmanager_domain_model_memberjournalentry',
      $journalEntryUid,
      'uid,processed,hidden'
    );
    if (!is_array($record)) {
      return;
    }

    // Nur pending Einträge (processed leer) dürfen über hidden deaktiviert werden.
    if (!empty($record['processed'])) {
      return;
    }

    if ((int) ($record['hidden'] ?? 0) === 1) {
      return;
    }

    $connection = GeneralUtility::makeInstance(ConnectionPool::class)
      ->getConnectionForTable('tx_clubmanager_domain_model_memberjournalentry');
    $connection->update(
      'tx_clubmanager_domain_model_memberjournalentry',
      [
        'hidden' => 1,
        'tstamp' => time(),
      ],
      ['uid' => $journalEntryUid]
    );
  }

  /**
   * Fallback fuer den Fall, dass hidden=1 in POST vorhanden ist,
   * aber nicht sauber in DataHandler->datamap landet.
   *
   * @param array<int, bool> $memberUids
   */
  private function enforcePendingHiddenFromRequestData(array &$memberUids): void
  {
    $postData = $_POST['data']['tx_clubmanager_domain_model_memberjournalentry'] ?? null;
    if (!is_array($postData)) {
      return;
    }

    foreach ($postData as $id => $fields) {
      if (!is_numeric((string) $id) || !is_array($fields)) {
        continue;
      }
      if (!array_key_exists('hidden', $fields) || (int) $fields['hidden'] !== 1) {
        continue;
      }

      $journalEntryUid = (int) $id;
      $record = BackendUtility::getRecord(
        'tx_clubmanager_domain_model_memberjournalentry',
        $journalEntryUid,
        'uid,member,processed,hidden'
      );
      if (!is_array($record)) {
        continue;
      }

      // Nur pending Eintraege (processed leer) duerfen ueber hidden deaktiviert werden.
      if (!empty($record['processed'])) {
        continue;
      }

      if ((int) ($record['hidden'] ?? 0) !== 1) {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
          ->getConnectionForTable('tx_clubmanager_domain_model_memberjournalentry');
        $connection->update(
          'tx_clubmanager_domain_model_memberjournalentry',
          [
            'hidden' => 1,
            'tstamp' => time(),
          ],
          ['uid' => $journalEntryUid]
        );
      }

      $memberUid = (int) ($record['member'] ?? 0);
      if ($memberUid > 0) {
        $memberUids[$memberUid] = true;
      }
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

      // 3. Endtime bei geplanter Kündigung sofort setzen
      $this->applyPendingCancellationEndtime(
        $memberUid,
        $journalRepository,
        $memberRepository,
        $persistenceManager
      );

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
    $projectionService = GeneralUtility::makeInstance(MemberJournalProjectionService::class);
    return $projectionService->projectMemberConsistency($memberUid);
  }

  private function applyPendingCancellationEndtime(
    int $memberUid,
    MemberJournalEntryRepository $journalRepository,
    MemberRepository $memberRepository,
    PersistenceManager $persistenceManager
  ): void {
    $projectionService = GeneralUtility::makeInstance(MemberJournalProjectionService::class);
    $projectionService->applyPendingFutureCancellationEndtime($memberUid);
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

