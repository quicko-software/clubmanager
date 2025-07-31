<?php

namespace Quicko\Clubmanager\Domain\Factory;

use Quicko\Clubmanager\Domain\Model\FrontendUser;
use Quicko\Clubmanager\Domain\Repository\FrontendUserGroupRepository;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FeuserFactory
{
  // /
  // / Create a FrontendUser having
  // / * the correct pid
  // / * usergroup
  // / * random password
  // / * random username with given prefix
  // / * no further properties (no email and other stuff)
  // /
  public static function createDefaultMemberFeuser(string $usernamePrefix): FrontendUser
  {
    $user = new FrontendUser();
    /** @var ExtensionConfiguration $extensionConfiguration */
    $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);
    /** @var int<0, max> $pid */
    $pid = $extensionConfiguration->get('clubmanager', 'feUsersStoragePid');
    $user->setPid($pid);
    $user->setUsername($usernamePrefix . self::getUniqueNumberString());
    $user->setPassword('' . self::getUniqueNumberString());

    $memberOfDgbtUserGroup = 1;
    $frontendUserGroup = self::getFrontendUserGroupRepository()->findByUid($memberOfDgbtUserGroup);
    if ($frontendUserGroup) {
      $user->addUsergroup($frontendUserGroup);
    }

    return $user;
  }

  private static function getUniqueNumberString(): string
  {
    $digitString = '';
    for ($i = 0; $i < 10; ++$i) {
      $digitString .= rand(0, 9);
    }

    return $digitString;
  }

  private static function getFrontendUserGroupRepository(): FrontendUserGroupRepository
  {
    /** @var FrontendUserGroupRepository $frontendUserGroupRepository */
    $frontendUserGroupRepository = GeneralUtility::makeInstance(FrontendUserGroupRepository::class);

    return $frontendUserGroupRepository;
  }
}
