<?php

namespace Quicko\Clubmanager\Mail;

use Quicko\Clubmanager\Mail\Generator\Arguments\MailGeneratorArgumentsSerializer;
use Quicko\Clubmanager\Mail\Generator\MailGeneratorFactory;
use TYPO3\CMS\Core\Mail\Mailer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class MailSendService
{
  /**
   * @template T of object
   *
   * @param class-string<T> $generatorClass
   */
  public function processMailByGenerator(string $generatorClass, string $generatorJsonOptions): bool
  {
    $baseMailGeneratorArguments = MailGeneratorArgumentsSerializer::deserialize($generatorJsonOptions);
    $instance = MailGeneratorFactory::createGenerator($generatorClass);
    $fluidEmail = $instance->generateFluidMail($baseMailGeneratorArguments);
    if ($fluidEmail) {
      /*
      TODO: SITE CONTEXT
      $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($instance->get);
      $normalizedParams = new NormalizedParams(
          [
              'HTTP_HOST' => $site->getBase()->getHost(),
              'HTTPS' => $site->getBase()->getScheme() === 'https' ? 'on' : 'off',
          ],
          $GLOBALS['TYPO3_CONF_VARS']['SYS'],
          '',
          ''
      );

      $request = (new ServerRequest())
          ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE)
          ->withAttribute('normalizedParams', $normalizedParams)
          ->withAttribute('site', $site);

      $fluidEmail->setRequest($request);
      */

      GeneralUtility::makeInstance(Mailer::class)->send($fluidEmail);
    }
    $instance->cleanUp();

    return $fluidEmail ? true : false;
  }
}
