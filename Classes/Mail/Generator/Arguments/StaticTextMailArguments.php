<?php

namespace Quicko\Clubmanager\Mail\Generator\Arguments;


class StaticTextMailArguments extends BaseMailGeneratorArguments
{
  
  /**
   * MailTo
   *
   * @var \string
   */
  public $mailTo;

  /**
   * MailToName
   *
   * @var \string
   */
  public $mailToName;  

  /**
   * configRefPid
   *
   * @var ?\integer
   */
  public $configRefPid;

  /**
   * subject
   *
   * @var \string
   */
  public $subject;  

  /**
   * text
   *
   * @var \string
   */
  public $text;    


}
