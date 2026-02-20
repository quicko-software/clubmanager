<?php

namespace Quicko\Clubmanager\Domain\Model;

use DateTime;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class MemberJournalEntry extends AbstractEntity
{
  // Creator Types
  public const CREATOR_TYPE_SYSTEM = 0;
  public const CREATOR_TYPE_BACKEND = 1;
  public const CREATOR_TYPE_MEMBER = 2;

  // Entry Types
  public const ENTRY_TYPE_CANCELLATION_REQUEST = 'cancellation_request';
  public const ENTRY_TYPE_STATUS_CHANGE = 'status_change';
  public const ENTRY_TYPE_LEVEL_CHANGE = 'level_change';

  // WICHTIG: member als int, NICHT als Objekt!
  // → Journal bleibt nach Member-Löschung erhalten
  // → Keine CASCADE-Löschung
  // → UID wird für Statistiken benötigt
  protected int $member = 0;

  protected string $entryType = self::ENTRY_TYPE_STATUS_CHANGE;

  protected ?DateTime $entryDate = null;

  protected string $note = '';

  protected int $creatorType = self::CREATOR_TYPE_SYSTEM;

  protected ?DateTime $processed = null;

  protected ?DateTime $effectiveDate = null;

  protected ?int $targetState = null;

  protected ?int $oldLevel = null;

  protected ?int $newLevel = null;

  public function getMember(): int
  {
    return $this->member;
  }

  public function setMember(int $member): void
  {
    $this->member = $member;
  }

  public function getEntryType(): string
  {
    return $this->entryType;
  }

  public function setEntryType(string $entryType): void
  {
    $this->entryType = $entryType;
  }

  public function getEntryDate(): ?DateTime
  {
    return $this->entryDate;
  }

  public function setEntryDate(?DateTime $entryDate): void
  {
    $this->entryDate = $entryDate;
  }

  public function getNote(): string
  {
    return $this->note;
  }

  public function setNote(string $note): void
  {
    $this->note = $note;
  }

  public function getCreatorType(): int
  {
    return $this->creatorType;
  }

  public function setCreatorType(int $creatorType): void
  {
    $this->creatorType = $creatorType;
  }

  public function getProcessed(): ?DateTime
  {
    return $this->processed;
  }

  public function setProcessed(?DateTime $processed): void
  {
    $this->processed = $processed;
  }

  public function getEffectiveDate(): ?DateTime
  {
    return $this->effectiveDate;
  }

  public function setEffectiveDate(?DateTime $effectiveDate): void
  {
    $this->effectiveDate = $effectiveDate;
  }

  public function getTargetState(): ?int
  {
    return $this->targetState;
  }

  public function setTargetState(?int $targetState): void
  {
    $this->targetState = $targetState;
  }

  public function getOldLevel(): ?int
  {
    return $this->oldLevel;
  }

  public function setOldLevel(?int $oldLevel): void
  {
    $this->oldLevel = $oldLevel;
  }

  public function getNewLevel(): ?int
  {
    return $this->newLevel;
  }

  public function setNewLevel(?int $newLevel): void
  {
    $this->newLevel = $newLevel;
  }

  // Helper Methods

  public function isStatusChange(): bool
  {
    return $this->entryType === self::ENTRY_TYPE_STATUS_CHANGE;
  }

  public function isLevelChange(): bool
  {
    return $this->entryType === self::ENTRY_TYPE_LEVEL_CHANGE;
  }

  public function isCancellationRequest(): bool
  {
    return $this->entryType === self::ENTRY_TYPE_CANCELLATION_REQUEST;
  }

  public function requiresProcessing(): bool
  {
    return ($this->isStatusChange() || $this->isLevelChange())
      && $this->processed === null;
  }

  public function isCreatedByMember(): bool
  {
    return $this->creatorType === self::CREATOR_TYPE_MEMBER;
  }

  public function isCreatedBySystem(): bool
  {
    return $this->creatorType === self::CREATOR_TYPE_SYSTEM;
  }

  public function isCreatedByBackend(): bool
  {
    return $this->creatorType === self::CREATOR_TYPE_BACKEND;
  }
}


