<?php

namespace Quicko\Clubmanager\Mail\Generator;

use Quicko\Mailjournal\Mail\Generator\Arguments\BaseMailGeneratorArguments;
use Quicko\Clubmanager\Mail\Generator\Arguments\GenericMemberMailArguments;
use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core\Mail\FluidEmail;

class GenericMemberMailGenerator extends BaseMemberUidMailGenerator
{
  /**
   * @var string[]
   */
  protected $fileToDelete = [];

  public function getLabel(BaseMailGeneratorArguments $args): string
  {
    /** @var GenericMemberMailArguments $genericMemberMailArguments */
    $genericMemberMailArguments = $args;

    return $genericMemberMailArguments->label ?? 'Unkown';
  }

  public function getMailTo(BaseMailGeneratorArguments $args): string
  {
    /** @var GenericMemberMailArguments $genericMemberMailArguments */
    $genericMemberMailArguments = $args;

    return $genericMemberMailArguments->mailTo;
  }

  public function generateFluidMail(BaseMailGeneratorArguments $args): ?FluidEmail
  {
    /** @var GenericMemberMailArguments $genericMemberMailArguments */
    $genericMemberMailArguments = $args;

    $member = $this->getMemberFromArgs($args);

    $fluidEmail = parent::createFluidMail($genericMemberMailArguments->configRefPid ?? 0);
    if (!empty($genericMemberMailArguments->mailFrom)) {
      $fluidEmail->from($genericMemberMailArguments->mailFrom);
    }
    $mailToParts = explode(',', $genericMemberMailArguments->mailTo);
    $mailToNameParts = explode(',', $genericMemberMailArguments->mailToName ?? '');

    for ($i = 0; $i < count($mailToParts); ++$i) {
      $name = '';
      if (count($mailToNameParts) - 1 >= $i) {
        $name = $mailToNameParts[$i];
      }
      $fluidEmail->addTo(new Address(
        $mailToParts[$i],
        $name
      ));
    }
    if (property_exists($genericMemberMailArguments, 'attachments') && $genericMemberMailArguments->attachments != null) {
      foreach ($genericMemberMailArguments->attachments as $attachment) {
        $fluidEmail->attachFromPath($attachment['path'], $attachment['name'], $attachment['contentType']);
        if ($genericMemberMailArguments->deleteAttachmentsAfterSend) {
          $this->fileToDelete[] = $attachment['path'];
        }
      }
    }

    $fluidEmail->subject($genericMemberMailArguments->subject)
      ->format('html')
      ->setTemplate($genericMemberMailArguments->templateName);

    if ($genericMemberMailArguments->fluidVars) {
      foreach ($genericMemberMailArguments->fluidVars as $key => $value) {
        $fluidEmail->assign($key, $value);
      }
    }
    if ($member) {
      $fluidEmail->assign('member', $member);
    }

    return $fluidEmail;
  }

  public function cleanUp(): void
  {
    foreach ($this->fileToDelete as $file) {
      if (file_exists($file)) {
        unlink($file);
      }
    }
  }
}
