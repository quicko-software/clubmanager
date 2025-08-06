<?php

namespace Quicko\Clubmanager\Mail\Generator\Arguments;

use Quicko\Mailjournal\Mail\Generator\Arguments\BaseMailGeneratorArguments;


class StaticTextMailArguments extends BaseMailGeneratorArguments
{
  
  /**
   * MailTo
   *
   */
  public string $mailTo;

  /**
   * MailToName
   *
   */
  public string $mailToName;  

  /**
   * configRefPid
   *
   */
  public ?int $configRefPid;

  /**
   * subject
   *
   */
  public string $subject;  

  /**
   * text
   *
   */
  public string $text;    


}
