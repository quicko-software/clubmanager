<?php

namespace Quicko\Clubmanager\Event;

final readonly class MemberStateChangedEvent
{
  public function __construct(private int $memberUid, private int $oldState, private int $newState)
  {
  }

  public function getMemberUid(): int
  {
    return $this->memberUid;
  }

  public function getOldState(): int
  {
    return $this->oldState;
  }

  public function getNewState(): int
  {
    return $this->newState;
  }
}
