<?php

namespace Quicko\Clubmanager\Mail\Generator;

use Quicko\Clubmanager\Mail\Generator\Arguments\BaseMailGeneratorArguments;
use Quicko\Clubmanager\Mail\Generator\Arguments\GenericMailArguments;
use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core\Mail\FluidEmail;

class GenericMailGenerator extends BaseMemberUidMailGenerator
{
  /**
   * @var string[]
   */
  protected $fileToDelete = [];

  public function getLabel(BaseMailGeneratorArguments $args): string
  {
    /** @var GenericMailArguments $genericMailArguments */
    $genericMailArguments = $args;

    return $genericMailArguments->label ?? 'Unkown';
  }

  public function getMailTo(BaseMailGeneratorArguments $args): string
  {
    /** @var GenericMailArguments $genericMailArguments */
    $genericMailArguments = $args;

    return $genericMailArguments->mailTo;
  }

  public function generateFluidMail(BaseMailGeneratorArguments $args): ?FluidEmail
  {
    /** @var GenericMailArguments $genericMailArguments */
    $genericMailArguments = $args;

    $member = $this->getMemberFromArgs($args);

    $fluidEmail = parent::createFluidMail($genericMailArguments->configRefPid);

    $mailToParts = explode(',', $genericMailArguments->mailTo);
    $mailToNameParts = explode(',', $genericMailArguments->mailToName ?? '');

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
    if (property_exists($genericMailArguments, 'attachments') && $genericMailArguments->attachments != null) {
      foreach ($genericMailArguments->attachments as $attachment) {
        $fluidEmail->attachFromPath($attachment['path'], $attachment['name'], $attachment['contentType']);
        if ($genericMailArguments->deleteAttachmentsAfterSend) {
          $this->fileToDelete[] = $attachment['path'];
        }
      }
    }

    $fluidEmail->subject($genericMailArguments->subject)
      ->format('html')
      ->setTemplate($genericMailArguments->templateName);
    foreach ($genericMailArguments->fluidVars as $key => $value) {
      $fluidEmail->assign($key, $value);
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
