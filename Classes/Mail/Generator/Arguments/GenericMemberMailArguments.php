<?php

namespace Quicko\Clubmanager\Mail\Generator\Arguments;

use Quicko\Mailjournal\Mail\Generator\Arguments\GenericMailArguments;

class GenericMemberMailArguments extends GenericMailArguments
{
  /**
   * memberUid
   *
   * @var int
   */
  public ?int $memberUid = null;

}
