<?php

namespace Quicko\Clubmanager\Mail\Generator;

use Quicko\Clubmanager\Mail\Generator\Arguments\BaseMailGeneratorArguments;
use Quicko\Clubmanager\Mail\Generator\Arguments\StaticTextMailArguments;
use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core\Mail\FluidEmail;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class StaticTextMailGenerator extends BaseMailGenerator
{
  public function getLabel(BaseMailGeneratorArguments $args): string
  {
    return LocalizationUtility::translate('statictextmailgenerator.label', 'clubmanager');
  }

  public function getMailTo(BaseMailGeneratorArguments $args): string
  {
    /**
     * @var Arguments\GenericMailArguments $mailToargs
     */
    $mailToargs = $args;

    return $mailToargs->mailTo;
  }

  public function generateFluidMail(BaseMailGeneratorArguments $args): ?FluidEmail
  {
    /** @var StaticTextMailArguments $simpleTextArgs */
    $simpleTextArgs = $args;

    $fluidEmail = parent::createFluidMail($simpleTextArgs->configRefPid);
    $fluidEmail->to(new Address(
      $simpleTextArgs->mailTo,
      $simpleTextArgs->mailToName
    ))
      ->subject($simpleTextArgs->subject)
      ->format('html')
      ->setTemplate('SimpleHtml')
      ->assign('htmlBody', $simpleTextArgs->text);

    return $fluidEmail;
  }
}
