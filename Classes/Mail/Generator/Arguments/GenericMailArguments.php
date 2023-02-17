<?php

namespace Quicko\Clubmanager\Mail\Generator\Arguments;


class GenericMailArguments extends MemberUidArguments
{

  /**
   * Label
   *
   * @var \string
   */
  public $label;

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
   * subject
   *
   * @var \string
   */
  public $subject;   

  /**
   * configRefPid
   *
   * @var ?\integer
   */
  public $configRefPid;


  /**
   * TemplateName
   *
   * @var \string
   */
  public $templateName;

  /**
   * FluidVars
   *
   * @var ?\array
   */
  public $fluidVars;


   /**
   * Attachments
   *
   * @var ?\array
   */
  public $attachments;

  /**
   * deleteAttachmentsAfterSend
   *
   * @var boolean
   */
  public $deleteAttachmentsAfterSend = false;

}
