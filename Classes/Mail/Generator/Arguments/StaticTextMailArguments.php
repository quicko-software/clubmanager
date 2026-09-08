<?php

declare(strict_types=1);

namespace Quicko\Clubmanager\Mail\Generator\Arguments;

use Quicko\Mailjournal\Mail\Generator\Arguments\BaseMailGeneratorArguments;

class StaticTextMailArguments extends BaseMailGeneratorArguments
{
  public string $mailTo;

  public string $mailToName;

  public ?int $configRefPid;

  public string $subject;

  public string $text;
}
