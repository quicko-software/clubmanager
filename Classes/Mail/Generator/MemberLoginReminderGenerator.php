<?php

namespace Quicko\Clubmanager\Mail\Generator;

use InvalidArgumentException;
use Quicko\Mailjournal\Mail\Generator\Arguments\BaseMailGeneratorArguments;
use Symfony\Component\Mime\Address;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Core\Mail\FluidEmail;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Site\SiteFinder;


class MemberLoginReminderGenerator extends BaseMemberUidMailGenerator
{

  public function getLabel(BaseMailGeneratorArguments $args): string
  {
    return LocalizationUtility::translate('memberloginremindergenerator.label', 'clubmanager') ?? '';
  }

  public function generateFluidMail(BaseMailGeneratorArguments $args): ?FluidEmail
  {
    /** @var \Quicko\Clubmanager\Mail\Generator\Arguments\MemberLoginReminderArguments $memberArgs */
    $memberArgs = $args;

    $member = $this->getMemberFromArgs($args);
    if (!$member) {
      throw new InvalidArgumentException("Member not found");
    }
    $address_email = $member['fe_users_email'];
    $address_name = ($member['firstname'] ?? "") . ' ' . ($member['lastname'] ?? "");

    $fluidEmail = parent::createFluidMail($member["pid"]);
    $fluidEmail->to(
      new Address(
        $address_email,
        $address_name
      )
    )
      ->subject(LocalizationUtility::translate('mail.reminder.subject', 'clubmanager') ?? '')
      ->format('html')
      ->setTemplate('Reminder')
      ->assign('member', $member)
      ->assign('recoveryLink', $this->generateRecoveryLink($memberArgs->loginPid))
      ->assign('loginLink', $this->generateLoginLink($memberArgs->loginPid));
    return $fluidEmail;
  }


  private function generateLoginLink(int $loginPid): string
  {
    $parameters = [];
    $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($loginPid);
    return (string) $site->getRouter()->generateUri($loginPid, $parameters);
  }


  private function generateRecoveryLink(int $loginPid): string
  {
    //
    // snippet from https://various.at/news/typo3-uribuilder-im-backend-context
    $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($loginPid);
    $parameters = [
      'tx_felogin_login' => [
        'action' => 'recovery',
        'controller' => 'PasswordRecovery'
      ]
    ];
    return (string) $site->getRouter()->generateUri(
      $loginPid,
      $parameters
    );
  }
}
