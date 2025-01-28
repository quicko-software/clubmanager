<?php

namespace Quicko\Clubmanager\Domain\Model\Mail;

use DateTime;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Task extends AbstractEntity
{
  public const SEND_STATE_WILL_SEND = 0;
  public const SEND_STATE_DONE = 1;
  public const SEND_STATE_STOPPED = 2;
  public const PRIORITY_LEVEL_MIN = 0;
  public const PRIORITY_LEVEL_MEDIUM = 5;
  public const PRIORITY_LEVEL_HIGHT = 10;

  protected int $sendState;

  protected int $priorityLevel;

  protected string $generatorClass;

  protected string $generatorArguments;

  protected DateTime $processedTime;

  protected DateTime $errorTime;

  protected string $errorMessage;

  protected int $openTries;

  public function getOpenTries(): int
  {
    return $this->openTries;
  }

  public function setOpenTries(int $openTries): void
  {
    $this->openTries = $openTries;
  }

  public function getErrorMessage(): string
  {
    return $this->errorMessage;
  }

  public function setErrorMessage(string $errorMessage): void
  {
    $this->errorMessage = $errorMessage;
  }

  public function getErrorTime(): DateTime
  {
    return $this->processedTime;
  }

  public function setErrorTime(DateTime $errorTime): void
  {
    $this->errorTime = $errorTime;
  }

  public function getProcessedTime(): DateTime
  {
    return $this->processedTime;
  }

  public function setProcessedTime(DateTime $processedTime): void
  {
    $this->processedTime = $processedTime;
  }

  public function getGeneratorArguments(): string
  {
    return $this->generatorArguments;
  }

  public function setGeneratorArguments(string $generatorArguments): void
  {
    $this->generatorArguments = $generatorArguments;
  }

  public function getGeneratorClass(): string
  {
    return $this->generatorClass;
  }

  public function setGeneratorClass(string $generatorClass): void
  {
    $this->generatorClass = $generatorClass;
  }

  public function getSendState(): int
  {
    return $this->sendState;
  }

  public function setSendState(int $sendState): void
  {
    $this->sendState = $sendState;
  }

  public function getPriorityLevel(): int
  {
    return $this->priorityLevel;
  }

  public function setPriorityLevel(int $priorityLevel): void
  {
    $this->priorityLevel = $priorityLevel;
  }
}
