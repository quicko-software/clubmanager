<?php

namespace Quicko\Clubmanager\Service;

use DateTime;
use Quicko\Clubmanager\Domain\Model\Member;
use Quicko\Clubmanager\Domain\Model\MemberJournalEntry;
use Quicko\Clubmanager\Domain\Repository\MemberRepository;
use Quicko\Clubmanager\Domain\Repository\MemberJournalEntryRepository;
use Quicko\Clubmanager\Utils\SettingUtils;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

class MemberJournalService
{
  public function __construct(
    protected MemberJournalEntryRepository $journalRepository,
    protected MemberRepository $memberRepository,
    protected PersistenceManager $persistenceManager
  ) {
  }

  /**
   * Erstellt Kündigungswunsch (Status-Wechsel wird später von Billing-Task erstellt)
   */
  public function createCancellationRequest(
    Member $member,
    string $noteText,
    int $creatorType = MemberJournalEntry::CREATOR_TYPE_MEMBER
  ): MemberJournalEntry {
    $memberUid = $member->getUid();
    if (!$memberUid) {
      throw new \InvalidArgumentException('Member must have a UID');
    }

    $request = new MemberJournalEntry();
    $request->setPid($this->resolveJournalStoragePid($member));
    $request->setMember($memberUid);
    $request->setEntryType(MemberJournalEntry::ENTRY_TYPE_CANCELLATION_REQUEST);
    $request->setEntryDate(new DateTime());
    // effective_date wird nicht mehr gesetzt - entry_date wird verwendet
    $request->setNote($noteText);
    $request->setCreatorType($creatorType);
    $this->journalRepository->add($request);
    $this->persistenceManager->persistAll();

    return $request;
  }

  /**
   * Blendt offene Kündigungen aus (z.B. bei Reaktivierung)
   */
  public function resolvePendingCancellationForMember(int $memberUid, string $noteText = ''): int
  {
    $updated = 0;
    $noteText = trim($noteText);
    if ($noteText === '') {
      if (ExtensionManagementUtility::isLoaded('clubmanager_pro')) {
        $noteText = LocalizationUtility::translate(
          'memberjournalentry.note.cancellation_reverted',
          'clubmanager_pro'
        ) ?? '';
      }
      $noteText = $noteText !== '' ? $noteText : (
        LocalizationUtility::translate('memberjournal.cancellation_reverted', 'clubmanager')
          ?? 'Cancellation request reverted by status change to active'
      );
    }

    $request = $this->journalRepository->findPendingCancellationRequest($memberUid);
    if ($request instanceof MemberJournalEntry) {
      $this->hideJournalEntry($request, $noteText);
      $updated++;
    }

    $statusChange = $this->journalRepository->findPendingCancellationStatusChange($memberUid);
    if ($statusChange instanceof MemberJournalEntry) {
      $this->hideJournalEntry($statusChange, $noteText);
      $updated++;
    }

    return $updated;
  }

  /**
   * Erstellt Status-Änderungs-Eintrag
   */
  public function createStatusChange(
    Member $member,
    int $targetState,
    DateTime $effectiveDate,
    string $noteText = '',
    int $creatorType = MemberJournalEntry::CREATOR_TYPE_SYSTEM
  ): MemberJournalEntry {
    $memberUid = $member->getUid();
    if (!$memberUid) {
      throw new \InvalidArgumentException('Member must have a UID');
    }

    $entry = new MemberJournalEntry();
    $entry->setPid($this->resolveJournalStoragePid($member));
    $entry->setMember($memberUid);
    $entry->setEntryType(MemberJournalEntry::ENTRY_TYPE_STATUS_CHANGE);
    $entry->setEntryDate(new DateTime());
    $entry->setEffectiveDate($effectiveDate);
    $entry->setTargetState($targetState);
    $entry->setNote($noteText);
    $entry->setCreatorType($creatorType);
    $this->journalRepository->add($entry);
    $this->persistenceManager->persistAll();

    return $entry;
  }

  /**
   * Verarbeitet fällige Journal-Einträge (Cron)
   */
  public function processPendingEntries(?DateTime $referenceDate = null): int
  {
    $refDate = $referenceDate ?? new DateTime('now');
    $pendingEntries = $this->journalRepository->findPendingUntilDate($refDate);

    $processedCount = 0;
    $now = new DateTime();

    foreach ($pendingEntries as $entry) {
      $memberUid = $entry->getMember();

      // Lade Member-Objekt
      $member = $this->memberRepository->findByUidWithoutStoragePage($memberUid);

      if (!$member instanceof Member) {
        // Member existiert nicht mehr, markiere trotzdem als verarbeitet
        $entry->setProcessed($now);
        $this->journalRepository->update($entry);
        continue;
      }

      try {
        if ($entry->isStatusChange()) {
          $this->applyStatusChange($member, $entry);
        } elseif ($entry->isLevelChange()) {
          $this->applyLevelChange($member, $entry);
        }

        $entry->setProcessed($now);
        $this->journalRepository->update($entry);
        $this->memberRepository->update($member);
        $processedCount++;
      } catch (\InvalidArgumentException $e) {
        // Log error but continue with next entry
        $logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        $logger->error('Skipping invalid journal entry', [
          'entryUid' => $entry->getUid(),
          'memberUid' => $memberUid,
          'error' => $e->getMessage(),
        ]);
        // Mark as processed to prevent retry loop
        $entry->setProcessed($now);
        $this->journalRepository->update($entry);
      }
    }

    if ($processedCount > 0) {
      $this->persistenceManager->persistAll();
    }

    return $processedCount;
  }

  /**
   * Verarbeitet fällige Journal-Einträge für einen spezifischen Member
   */
  public function processPendingEntriesForMember(int $memberUid, ?DateTime $referenceDate = null): int
  {
    $refDate = $referenceDate ?? new DateTime('now');
    $pendingEntries = $this->journalRepository->findPendingUntilDateForMember($refDate, $memberUid);
    $entriesArray = iterator_to_array($pendingEntries);

    $processedCount = 0;
    $now = new DateTime();

    foreach ($entriesArray as $entry) {
      try {
        // Lade Member-Objekt - versuche zuerst via Repository
        $member = $this->memberRepository->findByUidWithoutStoragePage($memberUid);

        if (!$member instanceof Member) {
          // Versuche direkten DB-Zugriff (z.B. wenn wir im Hook während des Speicherns sind)
          $memberRecord = \TYPO3\CMS\Backend\Utility\BackendUtility::getRecord('tx_clubmanager_domain_model_member', $memberUid);
          if (!$memberRecord) {
            // Member existiert nicht mehr, markiere trotzdem als verarbeitet
            $entry->setProcessed($now);
            $this->journalRepository->update($entry);
            continue;
          }

          // Erstelle ein temporäres Member-Objekt aus dem Record
          $member = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(Member::class);
          $member->_setProperty('uid', $memberRecord['uid']);
          $member->setState($memberRecord['state'] ?? '');
          $member->setLevel($memberRecord['level'] ?? 0);
          $member->setIdent($memberRecord['ident'] ?? '');
          $member->setStarttime($memberRecord['starttime'] ? new \DateTime('@' . $memberRecord['starttime']) : null);
          $member->setEndtime($memberRecord['endtime'] ? new \DateTime('@' . $memberRecord['endtime']) : null);

          // Wende Änderungen am temporären Objekt an
          if ($entry->isStatusChange()) {
            $this->applyStatusChange($member, $entry);
          } elseif ($entry->isLevelChange()) {
            $this->applyLevelChange($member, $entry);
          }

          // Speichere direkt in DB statt via Repository
          $connection = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)
            ->getConnectionForTable('tx_clubmanager_domain_model_member');
          $connection->update(
            'tx_clubmanager_domain_model_member',
            [
              'state' => $member->getState(),
              'level' => $member->getLevel(),
              'starttime' => $member->getStarttime() ? $member->getStarttime()->getTimestamp() : 0,
              'endtime' => $member->getEndtime() ? $member->getEndtime()->getTimestamp() : 0,
            ],
            ['uid' => $memberUid]
          );

          $entry->setProcessed($now);
          $this->journalRepository->update($entry);
          $processedCount++;
        } else {
          // Normal flow via Repository
          if ($entry->isStatusChange()) {
            $this->applyStatusChange($member, $entry);
          } elseif ($entry->isLevelChange()) {
            $this->applyLevelChange($member, $entry);
          }

          $entry->setProcessed($now);
          $this->journalRepository->update($entry);
          $this->memberRepository->update($member);
          $processedCount++;
        }
      } catch (\InvalidArgumentException $e) {
        // Log error and mark as processed with error note
        $logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        $logger->error('Skipping invalid journal entry for member', [
          'entryUid' => $entry->getUid(),
          'memberUid' => $memberUid,
          'error' => $e->getMessage(),
        ]);
        
        $entry->setProcessed($now);
        $entry->setNote($entry->getNote() . "\nError: " . $e->getMessage());
        $this->journalRepository->update($entry);
        
        // Re-throw to inform the user via FlashMessage (in Hook)
        throw $e;
      }
    }

    if ($processedCount > 0) {
      $this->persistenceManager->persistAll();
    }

    return $processedCount;
  }

  protected function applyStatusChange(Member $member, MemberJournalEntry $entry): void
  {
    $targetState = $entry->getTargetState();
    $effectiveDate = $entry->getEffectiveDate();

    if ($targetState === null || $effectiveDate === null) {
      throw new \InvalidArgumentException('Status change requires target_state and effective_date');
    }

    // Prüfe ident bei Aktivierung
    if ($targetState === Member::STATE_ACTIVE) {
      $ident = trim((string) ($member->getIdent() ?? ''));
      if ($ident === '') {
        throw new \InvalidArgumentException(
          LocalizationUtility::translate('flash.activation_blocked.no_ident.short', 'clubmanager')
            ?? 'Activation not possible: Member number (ident) is missing.'
        );
      }
    }

    $member->setState($targetState);

    switch ($targetState) {
      case Member::STATE_ACTIVE:
        // Setze starttime nur bei Erstaktivierung (nicht bei Reaktivierung nach Ruhend-Status)
        if ($member->getStarttime() === null) {
          $member->setStarttime($effectiveDate);
        }
        // Wenn der Member wieder aktiv wird, entferne das endtime
        $member->setEndtime(null);
        break;

      case Member::STATE_CANCELLED:
        $member->setEndtime($effectiveDate);
        break;

      case Member::STATE_SUSPENDED:
        // Ruhend setzt KEINE endtime - Member bleibt Mitglied
        break;
    }
  }

  protected function applyLevelChange(Member $member, MemberJournalEntry $entry): void
  {
    $newLevel = $entry->getNewLevel();

    if ($newLevel === null) {
      throw new \InvalidArgumentException('Level-Change benötigt new_level');
    }

    $member->setLevel($newLevel);
  }

  /**
   * Prüft ob ein Member einen offenen Kündigungswunsch hat
   */
  public function hasPendingCancellationRequest(int $memberUid): bool
  {
    $request = $this->journalRepository->findPendingCancellationRequest($memberUid);
    return $request !== null;
  }

  /**
   * Findet offenen Kündigungswunsch eines Members
   */
  public function getPendingCancellationRequest(int $memberUid): ?MemberJournalEntry
  {
    return $this->journalRepository->findPendingCancellationRequest($memberUid);
  }

  /**
   * Findet den zugehörigen Status-Wechsel-Eintrag für einen Kündigungswunsch
   */
  public function getPendingCancellationStatusChange(int $memberUid): ?MemberJournalEntry
  {
    return $this->journalRepository->findPendingCancellationStatusChange($memberUid);
  }

  /**
   * Findet einen versteckten (hidden=1) future StatusChange "gekündigt".
   * Nutzt QueryBuilder, da Extbase EnableColumns hidden=1 ausfiltert.
   */
  public function getHiddenFutureCancellationStatusChange(int $memberUid): ?array
  {
    if ($memberUid <= 0) {
      return null;
    }

    $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
      ->getQueryBuilderForTable('tx_clubmanager_domain_model_memberjournalentry');
    $queryBuilder->getRestrictions()->removeAll();

    $record = $queryBuilder
      ->select('uid', 'effective_date', 'entry_date')
      ->from('tx_clubmanager_domain_model_memberjournalentry')
      ->where(
        $queryBuilder->expr()->eq('member', $queryBuilder->createNamedParameter($memberUid)),
        $queryBuilder->expr()->eq('entry_type', $queryBuilder->createNamedParameter(MemberJournalEntry::ENTRY_TYPE_STATUS_CHANGE)),
        $queryBuilder->expr()->eq('target_state', $queryBuilder->createNamedParameter(Member::STATE_CANCELLED)),
        $queryBuilder->expr()->eq('hidden', $queryBuilder->createNamedParameter(1)),
        $queryBuilder->expr()->isNull('processed'),
        $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0)),
        $queryBuilder->expr()->gt('effective_date', $queryBuilder->createNamedParameter(0))
      )
      ->orderBy('entry_date', 'DESC')
      ->setMaxResults(1)
      ->executeQuery()
      ->fetchAssociative();

    return is_array($record) ? $record : null;
  }

  /**
   * Zieht eine aktive future Kündigung zurück (hidden=1), triggert Projection.
   */
  public function withdrawCancellationStatusChange(int $memberUid): bool
  {
    $statusChange = $this->journalRepository->findPendingCancellationStatusChange($memberUid);
    if (!$statusChange instanceof MemberJournalEntry || !$statusChange->getUid()) {
      return false;
    }

    $connection = GeneralUtility::makeInstance(ConnectionPool::class)
      ->getConnectionForTable('tx_clubmanager_domain_model_memberjournalentry');
    $connection->update(
      'tx_clubmanager_domain_model_memberjournalentry',
      ['hidden' => 1, 'tstamp' => time()],
      ['uid' => $statusChange->getUid()]
    );

    $projectionService = GeneralUtility::makeInstance(MemberJournalProjectionService::class);
    $projectionService->projectMemberConsistency($memberUid);
    $projectionService->applyPendingFutureCancellationEndtime($memberUid);

    return true;
  }

  /**
   * Reaktiviert eine versteckte future Kündigung (hidden=0), triggert Projection.
   */
  public function reactivateCancellationStatusChange(int $memberUid): bool
  {
    $hiddenEntry = $this->getHiddenFutureCancellationStatusChange($memberUid);
    if (!is_array($hiddenEntry) || empty($hiddenEntry['uid'])) {
      return false;
    }

    $connection = GeneralUtility::makeInstance(ConnectionPool::class)
      ->getConnectionForTable('tx_clubmanager_domain_model_memberjournalentry');
    $connection->update(
      'tx_clubmanager_domain_model_memberjournalentry',
      ['hidden' => 0, 'tstamp' => time()],
      ['uid' => (int) $hiddenEntry['uid']]
    );

    $projectionService = GeneralUtility::makeInstance(MemberJournalProjectionService::class);
    $projectionService->projectMemberConsistency($memberUid);
    $projectionService->applyPendingFutureCancellationEndtime($memberUid);

    return true;
  }

  /**
   * Resolves the storage pid for journal entries based on site configuration
   * Falls back to member's pid if no site configuration is found
   */
  protected function resolveJournalStoragePid(Member $member): int
  {
    $memberPid = $member->getPid() ?? 0;
    if ($memberPid <= 0) {
      return 0;
    }

    $storagePid = (int) SettingUtils::getSiteSetting($memberPid, 'clubmanager.memberJournalStoragePid', 0);
    return $storagePid > 0 ? $storagePid : $memberPid;
  }

  private function hideJournalEntry(MemberJournalEntry $entry, string $noteText): void
  {
    $uid = $entry->getUid();
    if (!$uid) {
      return;
    }

    $fieldsToUpdate = [
      'hidden' => 1,
      'tstamp' => time(),
    ];

    $existingNote = trim($entry->getNote());
    $fieldsToUpdate['note'] = $existingNote === '' ? $noteText : $existingNote . "\n" . $noteText;

    $connection = GeneralUtility::makeInstance(ConnectionPool::class)
      ->getConnectionForTable('tx_clubmanager_domain_model_memberjournalentry');
    $connection->update(
      'tx_clubmanager_domain_model_memberjournalentry',
      $fieldsToUpdate,
      ['uid' => $uid]
    );
  }
}


