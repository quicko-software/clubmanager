<?php

namespace Quicko\Clubmanager\Domain\Factory;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;

use Quicko\Clubmanager\Domain\Model\FrontendUser;
use Quicko\Clubmanager\Domain\Model\FrontendUserGroupRepository;

class FeuserFactory
{
  ///
  /// Create a FrontendUser having
  /// * the correct pid 
  /// * usergroup
  /// * random password
  /// * random username with given prefix
  /// * no further properties (no email and other stuff)
  ///
  public static function createDefaultMemberFeuser($usernamePrefix)
  {
    $user = new FrontendUser();
    $user->setPid(GeneralUtility::makeInstance(ExtensionConfiguration::class)
      ->get('clubmanager', 'fe_users_storagePid')
    );
    $user->setUsername($usernamePrefix . self::getUniqueNumberString());
    $user->setPassword(''.self::getUniqueNumberString());
    
    $memberOfDgbtUserGroup = 1;
    $frontendUserGroup = self::getFrontendUserGroupRepository()->findByUid($memberOfDgbtUserGroup);       
    if ($frontendUserGroup)
    {
      $user->addUsergroup($frontendUserGroup);
    }

    return $user;
  }
  
  private static function getUniqueNumberString() {
    $digitString = '';
    for ($i=0;$i<10;++$i) {
      $digitString .= rand(0,9);
    }
    return $digitString;
  }

  private static function getFrontendUserGroupRepository() {
    return GeneralUtility::makeInstance(FrontendUserGroupRepository::class);
  }
}
