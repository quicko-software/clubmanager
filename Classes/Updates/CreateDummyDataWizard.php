<?php

namespace Quicko\Clubmanager\Updates;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Quicko\Clubmanager\Domain\Repository\CategoryRepository;
use Quicko\Clubmanager\Domain\Repository\CountryRepository;
use Quicko\Clubmanager\Domain\Repository\MemberRepository;
use Quicko\Clubmanager\DummyData\DummyMemberFactory;
use Quicko\Clubmanager\DummyData\DummyValues;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\ChattyInterface;
use TYPO3\CMS\Install\Updates\ConfirmableInterface;
use TYPO3\CMS\Install\Updates\Confirmation;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\ReferenceIndexUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

#[UpgradeWizard('cubmanager_createDummyDataWizard')]
class CreateDummyDataWizard implements ChattyInterface, UpgradeWizardInterface, LoggerAwareInterface, ConfirmableInterface
{
  use LoggerAwareTrait;
  public const IDENTIFIER = 'cubmanager_createDummyDataWizard';

  private MemberRepository $memberRepo;
  protected OutputInterface $output;

  protected ConnectionPool $connectionPool;

  protected Confirmation $confirmation;

  public function __construct()
  {
    $this->connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
    $this->memberRepo = GeneralUtility::makeInstance(MemberRepository::class);
    $this->confirmation = new Confirmation(
      'Really generate placeholder data?',
      $this->getDescription(),
      false,
      'Yes',
      'No',
      false
    );
  }

  /**
   * Return a confirmation message instance.
   */
  public function getConfirmation(): Confirmation
  {
    return $this->confirmation;
  }

  public function setOutput(OutputInterface $output): void
  {
    $this->output = $output;
  }

  public function getIdentifier(): string
  {
    return self::IDENTIFIER;
  }

  public function getTitle(): string
  {
    return 'Create member placeholder data for clubmanager extension';
  }

  public function getDescription(): string
  {
    return 'WARNING: The following tables will be truncated: sys_category, fe_users, fe_groups, tx_clubmanager_domain_model_member, tx_clubmanager_domain_model_location. Do NOT set to "Yes" if you could lose data!';
  }

  private function getConnection(): Connection
  {
    // works on other tables as well
    return $this->connectionPool->getConnectionForTable('tx_clubmanager_domain_model_member');
  }

  private function removeFormerlyCreatedNewData(): void
  {
    $this->getConnection()->executeStatement('TRUNCATE TABLE tx_clubmanager_domain_model_member;');
    $this->getConnection()->executeStatement('TRUNCATE TABLE tx_clubmanager_domain_model_location;');
    $this->getConnection()->executeStatement('TRUNCATE TABLE sys_category;');
    $this->getConnection()->executeStatement("DELETE FROM sys_category_record_mm 
      WHERE tablenames in (
        'tx_clubmanager_domain_model_location',
        'tx_clubmanager_domain_model_member'
      );
    ");
    $this->getConnection()->executeStatement('TRUNCATE TABLE fe_users;');
    $this->getConnection()->executeStatement('TRUNCATE TABLE fe_groups;');

    $this->getConnection()->executeStatement('DELETE FROM sys_file_reference WHERE 
      tablenames = \'tx_clubmanager_domain_model_location\'
    ');

    // $this->getConnection()->executeStatement("DELETE FROM fe_users WHERE username like '--import-generated-%'");
  }

  private function getDestinationPid(): mixed
  {
    /** @var ExtensionConfiguration $extConf */
    $extConf = GeneralUtility::makeInstance(ExtensionConfiguration::class);

    return $extConf->get(
      'clubmanager',
      'storagePid'
    );
  }

  private function createDummyData(): bool
  {
    /** @var CategoryRepository $categoryRepository */
    $categoryRepository = GeneralUtility::makeInstance(CategoryRepository::class);
    $this->createCategories($categoryRepository);
    $countryRepository = GeneralUtility::makeInstance(CountryRepository::class);
    $pid = $this->getDestinationPid();

    if ($pid === null || empty($pid)) {
      $this->output->writeln('The default storage pid is not defined.');

      return false;
    }

    $dummyMemberFactory = new DummyMemberFactory(
      $pid,
      $countryRepository,
      $categoryRepository
    );
    for ($i = 0; $i < 1000; ++$i) {
      $this->memberRepo->add($dummyMemberFactory->createMember());
    }
    $this->memberRepo->persistAll();

    return true;
  }

  private function createCategories(CategoryRepository $categoryRepository): void
  {
    foreach (DummyValues::CATEGORIES as $categoryNamePath) {
      $categoryRepository->getOrCreateByNamePath($categoryNamePath);
    }
  }

  public function executeUpdate(): bool
  {
    $this->removeFormerlyCreatedNewData();

    return $this->createDummyData();
  }

  public function updateNecessary(): bool
  {
    return true;
  }

  /**
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
