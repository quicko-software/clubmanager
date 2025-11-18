<?php

namespace Quicko\Clubmanager\Service;

use DateTime;
use Quicko\Clubmanager\Domain\Model\Member;
use Quicko\Clubmanager\Domain\Model\MemberJournalEntry;
use Quicko\Clubmanager\Domain\Repository\MemberRepository;
use Quicko\Clubmanager\Domain\Repository\MemberJournalEntryRepository;
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
   * Erstellt Kündigungswunsch + automatischen Status-Wechsel
   */
  public function createCancellationRequest(
    Member $member,
    string $noteText,
    DateTime $effectiveDate,
    int $creatorType = MemberJournalEntry::CREATOR_TYPE_MEMBER
  ): MemberJournalEntry {
    $memberUid = $member->getUid();
    if (!$memberUid) {
      throw new \InvalidArgumentException('Member must have a UID');
    }

    // 1. Kündigungswunsch
    $request = new MemberJournalEntry();
    $request->setPid($member->getPid());
    $request->setMember($memberUid);
    $request->setEntryType(MemberJournalEntry::ENTRY_TYPE_CANCELLATION_REQUEST);
    $request->setEntryDate(new DateTime());
    $request->setEffectiveDate($effectiveDate);
    $request->setNote($noteText);
    $request->setCreatorType($creatorType);
    $this->journalRepository->add($request);
    $this->persistenceManager->persistAll();

    // 2. Automatisch Status-Wechsel
    $statusChange = new MemberJournalEntry();
    $statusChange->setPid($member->getPid());
    $statusChange->setMember($memberUid);
    $statusChange->setEntryType(MemberJournalEntry::ENTRY_TYPE_STATUS_CHANGE);
    $statusChange->setEntryDate(new DateTime());
    $statusChange->setTargetState(Member::STATE_CANCELLED);
    $statusChange->setEffectiveDate($effectiveDate);
    $statusChange->setNote(sprintf(
      'Automatisch erstellt aufgrund Kündigungswunsch vom %s',
      $request->getEntryDate()->format('d.m.Y')
    ));
    $statusChange->setCreatorType(MemberJournalEntry::CREATOR_TYPE_SYSTEM);
    $this->journalRepository->add($statusChange);
    $this->persistenceManager->persistAll();

    return $request;
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
      throw new \InvalidArgumentException('Status-Change benötigt target_state und effective_date');
    }

    $member->setState($targetState);

    switch ($targetState) {
      case Member::STATE_ACTIVE:
        if (!$member->getStarttime()) {
          $member->setStarttime($effectiveDate);
        }
        break;

      case Member::STATE_CANCELLED:
      case Member::STATE_SUSPENDED:
        $member->setEndtime($effectiveDate);
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
}


