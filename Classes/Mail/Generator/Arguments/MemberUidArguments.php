<?php

declare(strict_types=1);

namespace Quicko\Clubmanager\Mail\Generator\Arguments;

use Quicko\Mailjournal\Mail\Generator\Arguments\BaseMailGeneratorArguments;

class MemberUidArguments extends BaseMailGeneratorArguments
{
  public ?int $memberUid = null;
}
