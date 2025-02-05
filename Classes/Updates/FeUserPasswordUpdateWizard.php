<?php

namespace Quicko\Clubmanager\Updates;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Quicko\Clubmanager\Domain\Model\FrontendUser;
use Quicko\Clubmanager\Domain\Repository\FrontendUserRepository;
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\ReferenceIndexUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

/**
 * delete?
 */
class FeUserPasswordUpdateWizard implements UpgradeWizardInterface, LoggerAwareInterface
{
  use LoggerAwareTrait;
  public const IDENTIFIER = 'clubmanager_feUserPasswordUpdateWizard';
  public const MAX_EXEC_TIME_SECONDS = 50;

  private ?FrontendUserRepository $userRepo = null;

  public function getIdentifier(): string
  {
    return self::IDENTIFIER;
  }

  /**
   * Return the speaking name of this wizard.
   */
  public function getTitle(): string
  {
    return 'Passwörter als Hash verschlüsseln';
  }

  /**
   * Return the description for this wizard.
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
   * Execute the update.
   *
   * Called when a wizard reports that an update is necessary
   */
  public function executeUpdate(): bool
  {
    $runId = time();
    $this->logger?->notice("start updateFeuserPasswordHash (runid = $runId)");
    $numStillUnconverted = $this->updateFeuserPasswordHash();
    $this->logger?->notice("end updateFeuserPasswordHash (runid = $runId)");
    $this->logger?->notice("status updateFeuserPasswordHash (runid = $runId): still $numStillUnconverted to convert");

    return $numStillUnconverted === 0;
  }

  private function getUserWithUnhashedPassword(): array
  {
    $result = [];

    $userList = $this->getUserRepo()->findAllIncludingDisabled();
    foreach ($userList as $user) {
      $oldPassword = $user->getPassword();
      $isProbablyHashed = (substr($oldPassword, 0, 1) === '$') && (strlen($oldPassword) > 6);
      if (!$isProbablyHashed) {
        $result[] = $user;
      }
    }

    return $result;
  }

  private function isTimeToStop(int $startTimeInSec): bool
  {
    $delta = time() - $startTimeInSec;

    return $delta >= self::MAX_EXEC_TIME_SECONDS;
  }

  private function getUserRepo(): FrontendUserRepository
  {
    if (!$this->userRepo) {
      /** @var FrontendUserRepository $frontendUserRepository */
      $frontendUserRepository = GeneralUtility::makeInstance(FrontendUserRepository::class);
      $this->userRepo = $frontendUserRepository;
    }

    return $this->userRepo;
  }

  private function saveUser(FrontendUser $user): void
  {
    $this->getUserRepo()->update($user);
    /** @var PersistenceManager $persistenceManager */
    $persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);
    $persistenceManager->persistAll();
  }

  private function updateFeuserPasswordHash(): int
  {
    $userListToConvert = $this->getUserWithUnhashedPassword();
    /** @var PasswordHashFactory $passwordHashFactory */
    $passwordHashFactory = GeneralUtility::makeInstance(PasswordHashFactory::class);
    $hasher = $passwordHashFactory->getDefaultHashInstance('FE');

    $countConverted = 0;
    $startTimeInSec = time();

    foreach ($userListToConvert as $user) {
      $oldPassword = $user->getPassword();
      $hashedPassword = $hasher->getHashedPassword($oldPassword);
      $user->setPassword($hashedPassword);
      $this->saveUser($user);

      ++$countConverted;
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
   * Returns an array of class names of prerequisite classes.
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
