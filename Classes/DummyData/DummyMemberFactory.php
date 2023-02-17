<?php

namespace Quicko\Clubmanager\DummyData;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

use Quicko\Clubmanager\Domain\Model\Location;
use Quicko\Clubmanager\Domain\Model\Member;
use Quicko\Clubmanager\Domain\Model\FrontendUser;
use Quicko\Clubmanager\Domain\Model\FrontendUserGroup;
use Quicko\Clubmanager\DummyData\DummyValues;
use Quicko\Clubmanager\Utils\LocationSlugUtil;

class DummyMemberFactory {

  private $last_ident = 0;
  private $pid = 0;
  private $countryRepository = null;
  private $categoryRepository = null;
  private $frontendUserGroup = null;
  private $geoData = [];

  private function shouldInitGeoData() {
    return ExtensionManagementUtility::isLoaded('clubmanager_zip_search');
  }

  public function __construct($pid, $countryRepository, $categoryRepository) {
    $this->pid = $pid;
    $this->countryRepository = $countryRepository;
    $this->categoryRepository = $categoryRepository;
    srand(1508);
    if($this->shouldInitGeoData()) {
      $this->initGeoData();
    }
  }


  public function createMember()
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


  private function addCategories($entity, $categoryConfig) {
    foreach ($categoryConfig as $config) {
      foreach ($config as $namePath => $likelihood) {
        if (rand(1,100) < $likelihood) {
          $entity->getCategories()->attach($this->categoryRepository->getOrCreateByNamePath($namePath));
        }
      }
    }
  }


  private function setBank(Member $member)
  {
    $member->setDirectDebit(1);
    $member->setIban('DE02300209000106531065');
    $member->setBic('CMCIDEDD');
    $member->setAccount('Opa Helmut');
  }


  private function setLocation($member, $geoData) 
  {
    if (rand(1,100) < 2) {
      return;
    }
    $location = new Location();
    $location->setPid($this->pid);
    $location->setSalutation($member->getSalutation());
    $location->setFirstname($member->getFirstname());
    $location->setLastname($member->getLastname());
    $location->setTitle($member->getTitle());
    $location->setStreet($member->getStreet());
    $location->setZip($geoData['zip']);
    $location->setCity($geoData['name']);
    $location->setCountry($member->getCountry());
    $location->setSlug(LocationSlugUtil::generateLocationSlug($location, $this->pid));
    $location->setInfo('');
    if($this->shouldInitGeoData()) {
      if (rand(1,100) < 98) {
        $location->setLatitude($geoData['latitude']);
        $location->setLongitude($geoData['longitude']);
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


  private function setMembership($member)
  {
    $member->setState($this->generateState());
    if ($member->hasState(Member::STATE_ACTIVE) || $member->hasState(Member::STATE_SUSPENDED)) {
      $member->setStarttime((new \DateTime())->setDate(rand(2015,2021), rand(1,12), rand(1,31)));
    }
    if ($member->hasState(Member::STATE_CANCELLED)) {
      $startDate = (new \DateTime())->setDate(rand(2015,2021), rand(1,12), rand(1,31));
      $member->setStarttime( $startDate );
      $endDate = clone $startDate;
      //$endDate->modify('+' . rand(1,12) .' month');
      $endDate->modify('+12 month');
      $member->setEndtime($endDate);
    }
    $member->setIdent($this->generateIdent());
  }


  private function setFeuser($member) 
  {
    if (rand(1,100) < 98)
    {
      $user = new FrontendUser();
      $user->setUsername($member->getIdent());
      $user->setFirstname($member->getFirstname());
      $user->setLastname($member->getLastname());
      $user->setEmail($member->getEmail());
      $hash_of_abc123 = '$argon2i$v=19$m=65536,t=16,p=1$LlppUWloWHQ0ajRHblk0Rg$SUAqAA2kIQ58IUon2je0y7+YZuFvqxpQ5z/QadMtIEw';
      $user->setPassword($hash_of_abc123);
      $user->setHidden(false);
      $user->setPid($this->pid);
      if (! $this->frontendUserGroup) {
        $this->frontendUserGroup = new FrontendUserGroup();
        $this->frontendUserGroup->setTitle('clubmanager_FrontendUserGroup');
        $this->frontendUserGroup->setPid($this->pid);
      } 
      $user->addUsergroup($this->frontendUserGroup);
      $member->setFeuser($user);
    }
  }


  private function setPersonals($member, $geoData)
  {
    $isFemale = rand(0,1);
    $member->setSalutation($isFemale ? Member::SALUTATION_FEMALE : Member::SALUTATION_MALE);
    $member->setFirstname($this->getRandomValue(
      $isFemale 
      ? DummyValues::FEMALE_FIRSTNAMES
      : DummyValues::MALE_FIRSTNAMES
    ));
    $member->setTitle(
      (rand(1,100) > 80)
      ? $this->getRandomValue(DummyValues::ACADEMIC_TITLES)
      : ''
    );
    $member->setLastname($this->getRandomValue(DummyValues::LASTNAMES));
    $member->setEmail($this->toEmail($member->getFirstname(),$member->getLastname()));
    $member->setPhone($this->generatePhone());
    $member->setZip($geoData['zip']);
    $member->setStreet($this->generateStreet());
    $member->setCity($geoData['name']);
    $GERMANY_UID = 54;
    $member->setCountry($this->countryRepository->findByUid($GERMANY_UID));

    if (rand(1,100) < 75) {
      $dateofbirth = new \DateTime();
      $dateofbirth->setTime(0,0);
      $dateofbirth->setDate(rand(1920,2004), rand(1,12), rand(1,28));
      $member->setDateofbirth($dateofbirth);
    }
  }


  private function toArray($csvValueString) {
    $array = explode(',', $csvValueString);
    array_walk($array, function(&$value, $index) {
      $value = trim($value);
    });
    return $array;
  }

  private function getRandomValue($csvValueString) {
    $array = $this->toArray($csvValueString);
    $count = count($array);
    $rand = rand();
    $index = $rand % $count;
    return $array[$index];
  }

  private function generateIdent() {
    return str_pad(''.(++ $this->last_ident),6,'0',STR_PAD_LEFT);
  }

  private function toEmail($firstname,$lastname) {
    return filter_var(
      "$firstname.$lastname@doesnotexist.clubmanager.software",
      FILTER_SANITIZE_EMAIL
    );
  }

  private function generateZip() {
    $result = ''.rand(0,9);
    for ($i=1;$i<=4;++$i) {
      $result .= rand(1,9);
    }
    return $result;
  }

  private function generatePhone() {
    $result = '0';
    for ($i=1;$i<=4;++$i) {
      $result .= rand(1,9);
    }
    $result .= '/';
    for ($i=1;$i<=7;++$i) {
      $result .= rand(1,9);
    }
    return $result;
  }

  private function generateStreet() {
    $NAME_TYPE_PERSON = 0;
    $NAME_TYPE_CITY = 1;
    $STREET_TYPE_ROAD = 0;
    $STREET_TYPE_WAY = 1;

    $nameType = rand(0,1);
    $streetType = rand(0,1);

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

    $number = rand(1,150);

    return "$name-$street $number";
  }

  private function generateState() {
    $r = rand(1,100);
    if ($r <= 1) return Member::STATE_UNSET;
    else if ($r <= 5) return Member::STATE_APPLIED;
    else if ($r <= 80) return Member::STATE_ACTIVE;
    else if ($r <= 85) return Member::STATE_SUSPENDED;
    else return Member::STATE_CANCELLED;
  }


  private function initGeoData() {
    $resultRows = GeneralUtility::makeInstance(ConnectionPool::class)
      ->getConnectionForTable('tx_clubmanagerzipsearch_domain_model_geosearch_coordinates')
      ->select(['zip','name','longitude','latitude'],'tx_clubmanagerzipsearch_domain_model_geosearch_coordinates')
    ;

    while ($row = $resultRows->fetchAssociative()) {
      $name = $row['name']; // 06120 Halle (Saale)
      $pureName = substr($name, 6);
      $row['name'] = $pureName;
      $this->geoData []= $row;
    }
  }


  private function chooseGeoData() {
    $geoData = [];
    if ($numGeoData = count($this->geoData)) {
      return $this->geoData[rand() % $numGeoData];
    } else {
      return [
        'name' => $this->getRandomValue(DummyValues::CITYNAMES),
        'zip' => $this->generateZip(),
        'longitude' => rand(6*1000,15*1000) / 1000.0,
        'latitude' => rand(47*1000,55*1000) / 1000.0
      ];
    }    
  }
}
