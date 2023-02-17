<?php

namespace Quicko\Clubmanager\Updates;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use \Quicko\Clubmanager\Domain\Repository\FrontendUserRepository;


class FeUserPasswordUpdateWizard implements UpgradeWizardInterface, LoggerAwareInterface
{
  const IDENTIFIER = 'clubmanager_feUserPasswordUpdateWizard';
  const MAX_EXEC_TIME_SECONDS = 50;

  use LoggerAwareTrait;

  private ?FrontendUserRepository $userRepo = null;

  /**
   *
   * @return string
   */
  public function getIdentifier(): string
  {
    return self::IDENTIFIER;
  }

  /**
   * Return the speaking name of this wizard
   *
   * @return string
   */
  public function getTitle(): string
  {
    return 'Passwörter als Hash verschlüsseln';
  }

  /**
   * Return the description for this wizard
   *
   * @return string
   */
  public function getDescription(): string
  {
    $count = count($this->getUserWithUnhashedPassword());
    $desc = "Alte Klartext-Passwörter neu als Hash verschlüsseln,
      damit die Anmeldung mit Typo3 V.10.4 möglich ist.
      ($count Datensätze betroffen)";
    return $desc;
  }

  /**
   * Execute the update
   *
   * Called when a wizard reports that an update is necessary
   *
   * @return bool
   */
  public function executeUpdate(): bool
  {
    $runId = time();
    $this->logger->notice("start updateFeuserPasswordHash (runid = $runId)");
    $numStillUnconverted = $this->updateFeuserPasswordHash();
    $this->logger->notice("end updateFeuserPasswordHash (runid = $runId)");
    $this->logger->notice("status updateFeuserPasswordHash (runid = $runId): still $numStillUnconverted to convert");
    return $numStillUnconverted === 0;
  }

  private function getUserWithUnhashedPassword() {
    $result = [];

    $userList = $this->getUserRepo()->findAllIncludingDisabled();
    foreach ($userList as $user) {
      $oldPassword = $user->getPassword();
      $isProbablyHashed = (substr($oldPassword, 0, 1) === '$') && (strlen($oldPassword) > 6);
      if (! $isProbablyHashed) {
        $result[] = $user;
      }
    }

    return $result;
  }

  private function isTimeToStop($startTimeInSec) {
    $delta = time() - $startTimeInSec;
    return $delta >= self::MAX_EXEC_TIME_SECONDS;
  }

  private function getUserRepo() {
    if (!$this->userRepo) {
      $this->userRepo = GeneralUtility::makeInstance(FrontendUserRepository::class);
    }
    return $this->userRepo;
  }

  private function saveUser($user) {
    $this->getUserRepo()->update($user);
    GeneralUtility::makeInstance(PersistenceManager::class)->persistAll();
  }

  private function updateFeuserPasswordHash() {
    $userListToConvert = $this->getUserWithUnhashedPassword();
    $hasher = GeneralUtility::makeInstance(PasswordHashFactory::class)->getDefaultHashInstance('FE');

    $countConverted = 0;
    $startTimeInSec = time();
    
    foreach ($userListToConvert as $user) {
      $oldPassword = $user->getPassword();
      $hashedPassword = $hasher->getHashedPassword($oldPassword);
      $user->setPassword($hashedPassword);
      $this->saveUser($user);

      $countConverted += 1;
      if ($this->isTimeToStop($startTimeInSec)) {
        break;
      }
    }

    return count($userListToConvert) - $countConverted;
  }

  /**
   * Is an update necessary?
   *
   * Is used to determine whether a wizard needs to be run.
   * Check if data for migration exists.
   *
   * @return bool Whether an update is required (TRUE) or not (FALSE)
   */
  public function updateNecessary(): bool
  {
    $hasUserWithUnhashedPassword = (count($this->getUserWithUnhashedPassword()) > 0);
    return $hasUserWithUnhashedPassword;
  }

  /**
   * Returns an array of class names of prerequisite classes
   *
   * This way a wizard can define dependencies like "database up-to-date" or
   * "reference index updated"
   *
   * @return string[]
   */
  public function getPrerequisites(): array
  {
    return [
      DatabaseUpdatedPrerequisite::class,
      ReferenceIndexUpdatedPrerequisite::class,
    ];
  }
}
