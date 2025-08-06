<?php

namespace Quicko\Clubmanager\Mail\Generator\Arguments;

use Quicko\Mailjournal\Mail\Generator\Arguments\BaseMailGeneratorArguments;

class MemberLoginReminderArguments extends BaseMailGeneratorArguments
{
  /**
   * memberUid.
   */
  public ?int $memberUid = null;
  /**
   * loginPid.
   */
  public int $loginPid;
}
