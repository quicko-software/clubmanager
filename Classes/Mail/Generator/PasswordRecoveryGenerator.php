<?php

namespace Quicko\Clubmanager\Mail\Generator;

use Quicko\Mailjournal\Mail\Generator\Arguments\BaseMailGeneratorArguments;
use Symfony\Component\Mime\Address;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Core\Mail\FluidEmail;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Crypto\Random;
use TYPO3\CMS\Extbase\Security\Cryptography\HashService;

use Quicko\Clubmanager\Mail\Generator\BaseMemberUidMailGenerator;
use Quicko\Clubmanager\Mail\Generator\Arguments\PasswordRecoveryArguments;
use Quicko\Clubmanager\Records\FeUserRecordRepository;
use Quicko\Clubmanager\Utils\TypoScriptUtils;

class PasswordRecoveryGenerator extends BaseMemberUidMailGenerator
{

  private ?int $timestamp = null;
  
  public function getLabel(BaseMailGeneratorArguments $args): string
  {
    return LocalizationUtility::translate('passwordrecoverygenerator.label', 'clubmanager') ?? '';
  }

  /**
   * Returns TTL timestamp of the forgot hash
   *
   * @return int
   */
  public function getLifeTimeTimestamp(int $passwordRecoveryLifeTime): int
  {
    if ($this->timestamp === null) {
      $context = GeneralUtility::makeInstance(Context::class);
      $currentTimestamp = $context->getPropertyFromAspect('date', 'timestamp');
      $this->timestamp = $currentTimestamp + 3600 * $passwordRecoveryLifeTime;
    }
    return $this->timestamp;
  }

  /**
   * 
   */
  protected function generateForgotHash(int $feUserUid, int $passwordRecoveryLifeTime): string
  {
    $random = GeneralUtility::makeInstance(Random::class);
    $hashService = GeneralUtility::makeInstance(HashService::class);
    $randomString = $random->generateRandomHexString(16);
    $forgotHash = $this->getLifeTimeTimestamp($passwordRecoveryLifeTime) . '|' . $hashService->generateHmac($randomString);


    $feUserRecordRepo =  GeneralUtility::makeInstance(FeUserRecordRepository::class);
    $feUserRecordRepo->update([$feUserUid], [
      "felogin_forgotHash" => GeneralUtility::hmac($forgotHash)
    ]);

    return $forgotHash;
  }


  public function generateFluidMail(BaseMailGeneratorArguments $args): ?FluidEmail
  {

    /** @var PasswordRecoveryArguments $passwordArgs */
    $passwordArgs = $args;

    $repo = $this->getMemberRepo();
    $member = $repo->findByUid($passwordArgs->memberUid ?? 0);
    if(!$member) return null;
    $address_email = $member['fe_users_email'];
    $address_name = ($member['firstname'] ?? "") . ' ' . ($member['lastname'] ?? "");


    $loginPidString = TypoScriptUtils::getTypoScriptValueForPage('plugin.tx_clubmanager.settings.feUsersLoginPid', $member['fe_users_pid']);
    $loginPid = intval($loginPidString);
    $passwordRecoveryLifeTimeString = TypoScriptUtils::getTypoScriptValueForPage('plugin.tx_clubmanager.settings.passwordRecoveryLifeTime', $member['fe_users_pid']);
    $passwordRecoveryLifeTime = intval($passwordRecoveryLifeTimeString);
    $forgotHash = $this->generateForgotHash($member['fe_users_uid'], $passwordRecoveryLifeTime);
    $fluidEmail = parent::createFluidMail($member['pid']);
    $fluidEmail->to(
      new Address(
        $address_email,
        $address_name
      )
    )
      ->subject(LocalizationUtility::translate('mail.logindata.subject', 'clubmanager') ?? '')
      ->format('html')
      ->setTemplate($passwordArgs->templateName)
      ->assign('member', $member)
      ->assign('passwordRecoveryLifeTime', $this->getLifeTimeTimestamp($passwordRecoveryLifeTime))
      ->assign('recoveryLink', $this->generateRecoveryLink($loginPid, $forgotHash))
      ->assign('loginLink', $this->generateLoginLink($loginPid));

    return $fluidEmail;
  }

  private function generateLoginLink(int $loginPid): string
  {
    $parameters = [];
    $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($loginPid);
    return (string) $site->getRouter()->generateUri($loginPid, $parameters);
  }

  private function generateRecoveryLink(int $loginPid, string $forgotHash): string
  {
    //
    // snippet from https://various.at/news/typo3-uribuilder-im-backend-context
    $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($loginPid);
    $parameters = [
      'tx_felogin_login' => [
        'action' => 'showChangePassword',
        'controller' => 'PasswordRecovery',
        'hash' => $forgotHash
      ]
    ];

    return (string) $site->getRouter()->generateUri(
      $loginPid,
      $parameters
    );
  }
}
