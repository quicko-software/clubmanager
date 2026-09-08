<?php

declare(strict_types=1);

namespace Quicko\Clubmanager\Mail\Generator\Arguments;

class GenericMemberMailArguments extends MemberUidArguments
{
  public string $label;

  public string $mailTo;

  public string $mailFrom = '';

  public string $mailToName;

  public string $subject;

  public ?int $configRefPid;

  public string $templateName;

  /**
   * @var ?array<string, mixed>
   */
  public ?array $fluidVars;

  /**
   * @var ?array<array<string, string>>
   */
  public ?array $attachments;

  public bool $deleteAttachmentsAfterSend = false;
}
