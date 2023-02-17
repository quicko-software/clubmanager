<?php

namespace Quicko\Clubmanager\Mail;

use TYPO3\CMS\Core\Mail\Mailer;

use TYPO3\CMS\Core\Utility\GeneralUtility;

use Quicko\Clubmanager\Mail\Generator\MailGeneratorFactory;
use Quicko\Clubmanager\Mail\Generator\Arguments\MailGeneratorArgumentsSerializer;

class MailSendService
{
  public function processMailByGenerator(string $generatorClass,string $generatorJsonOptions) : bool {
    $baseMailGeneratorArguments = MailGeneratorArgumentsSerializer::deserialize($generatorJsonOptions);
    $instance = MailGeneratorFactory::createGenerator($generatorClass);
    $fluidEmail = $instance->generateFluidMail($baseMailGeneratorArguments);
    if($fluidEmail) {
      GeneralUtility::makeInstance(Mailer::class)->send($fluidEmail);    
    }
    $instance->cleanUp();
    return $fluidEmail ? true : false;
  }
}