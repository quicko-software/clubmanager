<?php

namespace Quicko\Clubmanager\Domain\Model;

use DateTime;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class MemberStatusChange extends AbstractEntity
{
  protected ?Member $member = null;

  protected int $state = Member::STATE_UNSET;

  protected ?DateTime $effectiveDate = null;

  protected ?string $note = '';

  protected bool $processed = false;

  protected int $createdBy = 0;

  public function getMember(): ?Member
  {
    return $this->member;
  }

  public function setMember(?Member $member): void
  {
    $this->member = $member;
  }

  public function getState(): int
  {
    return $this->state;
  }

  public function setState(int $state): void
  {
    $this->state = $state;
  }

  public function getEffectiveDate(): ?DateTime
  {
    return $this->effectiveDate;
  }

  public function setEffectiveDate(?DateTime $effectiveDate): void
  {
    $this->effectiveDate = $effectiveDate;
  }

  public function getNote(): ?string
  {
    return $this->note;
  }

  public function setNote(?string $note): void
  {
    $this->note = $note;
  }

  public function isProcessed(): bool
  {
    return $this->processed;
  }

  public function setProcessed(bool $processed): void
  {
    $this->processed = $processed;
  }

  public function getCreatedBy(): int
  {
    return $this->createdBy;
  }

  public function setCreatedBy(int $createdBy): void
  {
    $this->createdBy = $createdBy;
  }
}

