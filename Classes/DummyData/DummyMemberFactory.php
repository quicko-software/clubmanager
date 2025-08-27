<?php

namespace Quicko\Clubmanager\DummyData;

use DateTime;
use Quicko\Clubmanager\Domain\Model\FrontendUser;
use Quicko\Clubmanager\Domain\Model\FrontendUserGroup;
use Quicko\Clubmanager\Domain\Model\Location;
use Quicko\Clubmanager\Domain\Model\Member;
use Quicko\Clubmanager\Domain\Repository\CategoryRepository;
use Quicko\Clubmanager\Domain\Repository\CountryRepository;
use Quicko\Clubmanager\Utils\LocationSlugUtil;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DummyMemberFactory
{
  private int $last_ident = 0;
  /**
   * @var int<0, max>
   */
  private int $pid = 0;
  private CountryRepository $countryRepository;
  private CategoryRepository $categoryRepository;
  private ?FrontendUserGroup $frontendUserGroup = null;
  /**
   * @var array<int,array<string,string|float>>
   */
  private $geoData = [];

  private function shouldInitGeoData(): bool
  {
    return ExtensionManagementUtility::isLoaded('clubmanager_zip_search');
  }

  /**
   * Summary of __construct.
   *
   * @param int<0, max> $pid
   */
  public function __construct(int $pid, CountryRepository $countryRepository, CategoryRepository $categoryRepository)
  {
    $this->pid = $pid;
    $this->countryRepository = $countryRepository;
    $this->categoryRepository = $categoryRepository;
    srand(1508);
    if ($this->shouldInitGeoData()) {
      $this->initGeoData();
    }
  }

  public function createMember(): Member
  {
    $member = new Member();
    $member->setPid($this->pid);

    $geoData = $this->chooseGeoData();

    $this->setMembership($member);
    $this->setPersonals($member, $geoData);
    $this->setFeuser($member);
    $this->setBank($member);
    $this->setLocation($member, $geoData);
    $this->addCategories($member, [
      ['/Mitglieder-Kategorien/Qualifikationen/Azubi' => 10],
      ['/Mitglieder-Kategorien/Qualifikationen/Doktor' => 20],
      ['/Mitglieder-Kategorien/Qualifikationen/Geselle' => 10],
      ['/Mitglieder-Kategorien/Qualifikationen/Ingeneur' => 30],
      ['/Mitglieder-Kategorien/Qualifikationen/Meister' => 10],
      ['/Mitglieder-Kategorien/Qualifikationen/Schüler' => 20],
      ['/Mitglieder-Kategorien/Qualifikationen/Student' => 20],
      ['/Mitglieder-Kategorien/Rollen/Assistent' => 10],
      ['/Mitglieder-Kategorien/Rollen/Berater' => 30],
      ['/Mitglieder-Kategorien/Rollen/Referent' => 20],
    ]);

    return $member;
  }

  /**
   * Summary of addCategories.
   *
   * @param Member|Location           $entity
   * @param array<array<string, int>> $categoryConfig
   */
  private function addCategories($entity, $categoryConfig): void
  {
    foreach ($categoryConfig as $config) {
      foreach ($config as $namePath => $likelihood) {
        if (rand(1, 100) < $likelihood) {
          $entity->getCategories()->attach($this->categoryRepository->getOrCreateByNamePath($namePath));
        }
      }
    }
  }

  private function setBank(Member $member): void
  {
    $member->setDirectDebit(1);
    $member->setIban('DE02300209000106531065');
    $member->setBic('CMCIDEDD');
    $member->setAccount('Opa Helmut');
  }

  /**
   * Summary of setLocation.
   *
   * @param array<string,string|float> $geoData
   */
  private function setLocation(Member $member, array $geoData): void
  {
    if (rand(1, 100) < 2) {
      return;
    }
    $location = new Location();
    $location->setPid($this->pid);
    $location->setSalutation($member->getSalutation());
    $location->setFirstname($member->getFirstname() ?? '');
    $location->setLastname($member->getLastname() ?? '');
    $location->setTitle($member->getTitle() ?? '');
    $location->setStreet($member->getStreet() ?? '');
    $location->setZip(strval($geoData['zip']));
    $location->setCity(strval($geoData['name']));
    $location->setCountry($member->getCountry());
    $location->setSlug(LocationSlugUtil::generateLocationSlug($location, $this->pid));
    $location->setInfo('');
    if ($this->shouldInitGeoData()) {
      if (rand(1, 100) < 98) {
        $location->setLatitude(strval($geoData['latitude']));
        $location->setLongitude(strval($geoData['longitude']));
      }
    }
    $this->addCategories($location, [
      ['/Standorttypen/Büro' => 10],
      ['/Standorttypen/Halle' => 10],
      ['/Standorttypen/Praxis' => 10],
      ['/Standorttypen/Sportstätte' => 10],
      ['/Standorttypen/Werkstatt' => 10],
      ['/Standorttypen/Gedenkstätte' => 10],
    ]);
    $member->setMainLocation($location);
  }

  private function setMembership(Member $member): void
  {
    $member->setState($this->generateState());
    if ($member->hasState(Member::STATE_ACTIVE) || $member->hasState(Member::STATE_SUSPENDED)) {
      $member->setStarttime((new DateTime())->setDate(rand(2015, 2021), rand(1, 12), rand(1, 31)));
    }
    if ($member->hasState(Member::STATE_CANCELLED)) {
      $startDate = (new DateTime())->setDate(rand(2015, 2021), rand(1, 12), rand(1, 31));
      $member->setStarttime($startDate);
      $endDate = clone $startDate;
      // $endDate->modify('+' . rand(1,12) .' month');
      $endDate->modify('+12 month');
      $member->setEndtime($endDate);
    }
    $member->setIdent($this->generateIdent());
  }

  private function setFeuser(Member $member): void
  {
    if (rand(1, 100) < 98) {
      $user = new FrontendUser();
      $user->setUsername($member->getIdent() ?? '');
      $user->setFirstname($member->getFirstname() ?? '');
      $user->setLastname($member->getLastname() ?? '');
      $user->setEmail($member->getEmail() ?? '');
      $hash_of_abc123 = '$argon2i$v=19$m=65536,t=16,p=1$LlppUWloWHQ0ajRHblk0Rg$SUAqAA2kIQ58IUon2je0y7+YZuFvqxpQ5z/QadMtIEw';
      $user->setPassword($hash_of_abc123);
      $user->setHidden(false);
      $user->setPid($this->pid);
      if (!$this->frontendUserGroup) {
        $this->frontendUserGroup = new FrontendUserGroup();
        $this->frontendUserGroup->setTitle('clubmanager_FrontendUserGroup');
        $this->frontendUserGroup->setPid($this->pid);
      }
      $user->addUsergroup($this->frontendUserGroup);
      $member->setFeuser($user);
    }
  }

  /**
   * Summary of setPersonals.
   *
   * @param array<string, float|string> $geoData
   */
  private function setPersonals(Member $member, array $geoData): void
  {
    $isFemale = rand(0, 1);
    $member->setSalutation($isFemale ? Member::SALUTATION_FEMALE : Member::SALUTATION_MALE);
    $member->setFirstname($this->getRandomValue(
      $isFemale
      ? DummyValues::FEMALE_FIRSTNAMES
      : DummyValues::MALE_FIRSTNAMES
    ));
    $member->setTitle(
      (rand(1, 100) > 80)
      ? $this->getRandomValue(DummyValues::ACADEMIC_TITLES)
      : ''
    );
    $member->setLastname($this->getRandomValue(DummyValues::LASTNAMES));
    $member->setEmail($this->toEmail($member->getFirstname() ?? '', $member->getLastname() ?? ''));
    $member->setPhone($this->generatePhone());
    $member->setZip(strval($geoData['zip']));
    $member->setStreet($this->generateStreet());
    $member->setCity(strval($geoData['name']));
    $GERMANY_UID = 54;
    $germany = $this->countryRepository->findByUid($GERMANY_UID);
    if ($germany) {
      $member->setCountry($germany);
    }

    if (rand(1, 100) < 75) {
      $dateofbirth = new DateTime();
      $dateofbirth->setTime(0, 0);
      $dateofbirth->setDate(rand(1920, 2004), rand(1, 12), rand(1, 28));
      $member->setDateofbirth($dateofbirth);
    }
  }

  /**
   * Summary of toArray.
   *
   * @return string[]
   */
  private function toArray(string $csvValueString): array
  {
    $array = explode(',', $csvValueString);
    array_walk($array, function (&$value, $index) {
      $value = trim($value);
    });

    return $array;
  }

  /**
   * Summary of getRandomValue.
   */
  private function getRandomValue(string $csvValueString): string
  {
    $array = $this->toArray($csvValueString);
    $count = count($array);
    $rand = rand();
    $index = $rand % $count;

    return $array[$index];
  }

  private function generateIdent(): string
  {
    return str_pad('' . (++$this->last_ident), 6, '0', STR_PAD_LEFT);
  }

  private function toEmail(string $firstname, string $lastname): mixed
  {
    return filter_var(
      "$firstname.$lastname@doesnotexist.clubmanager.software",
      FILTER_SANITIZE_EMAIL
    );
  }

  private function generateZip(): string
  {
    $result = '' . rand(0, 9);
    for ($i = 1; $i <= 4; ++$i) {
      $result .= rand(1, 9);
    }

    return $result;
  }

  private function generatePhone(): string
  {
    $result = '0';
    for ($i = 1; $i <= 4; ++$i) {
      $result .= rand(1, 9);
    }
    $result .= '/';
    for ($i = 1; $i <= 7; ++$i) {
      $result .= rand(1, 9);
    }

    return $result;
  }

  private function generateStreet(): string
  {
    $NAME_TYPE_PERSON = 0;
    $NAME_TYPE_CITY = 1;
    $STREET_TYPE_ROAD = 0;
    $STREET_TYPE_WAY = 1;

    $nameType = rand(0, 1);
    $streetType = rand(0, 1);

    $name = '';
    $street = '';
    $number = 1;

    if ($nameType === $NAME_TYPE_PERSON) {
      $firstname = $this->getRandomValue(DummyValues::FEMALE_FIRSTNAMES);
      $lastname = $this->getRandomValue(DummyValues::LASTNAMES);
      $name = "$firstname-$lastname";
    } else {
      $name = $this->getRandomValue(DummyValues::CITYNAMES);
    }

    $street = ($streetType === $STREET_TYPE_ROAD)
      ? 'Straße' : 'Weg'
    ;

    $number = rand(1, 150);

    return "$name-$street $number";
  }

  private function generateState(): int
  {
    $r = rand(1, 100);
    if ($r <= 1) {
      return Member::STATE_UNSET;
    } elseif ($r <= 5) {
      return Member::STATE_APPLIED;
    } elseif ($r <= 80) {
      return Member::STATE_ACTIVE;
    } elseif ($r <= 85) {
      return Member::STATE_SUSPENDED;
    } else {
      return Member::STATE_CANCELLED;
    }
  }

  private function initGeoData(): void
  {
    $resultRows = GeneralUtility::makeInstance(ConnectionPool::class)
      ->getConnectionForTable('tx_clubmanagerzipsearch_domain_model_geosearch_coordinates')
      ->select(['zip', 'name', 'longitude', 'latitude'], 'tx_clubmanagerzipsearch_domain_model_geosearch_coordinates')
    ;

    while ($row = $resultRows->fetchAssociative()) {
      $name = $row['name']; // 06120 Halle (Saale)
      $pureName = substr($name, 6);
      $row['name'] = $pureName;
      $this->geoData[] = $row;
    }
  }

  /**
   * @return array<string,string|float>
   */
  private function chooseGeoData(): array
  {
    if ($numGeoData = count($this->geoData)) {
      return $this->geoData[rand() % $numGeoData];
    } else {
      return [
        'name' => $this->getRandomValue(DummyValues::CITYNAMES),
        'zip' => $this->generateZip(),
        'longitude' => rand(6 * 1000, 15 * 1000) / 1000.0,
        'latitude' => rand(47 * 1000, 55 * 1000) / 1000.0,
      ];
    }
  }
}
