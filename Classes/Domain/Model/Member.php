<?php

namespace Quicko\Clubmanager\Domain\Model;

use DateTime;
use Quicko\Clubmanager\Domain\Helper\States;
use TYPO3\CMS\Extbase\Annotation\ORM\Cascade;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class Member extends AbstractEntity
{
  public const STATE_UNSET = 0;
  public const STATE_APPLIED = 1;
  public const STATE_ACTIVE = 2;
  public const STATE_SUSPENDED = 3;
  public const STATE_CANCELLED = 4;

  public const SALUTATION_OTHER = 0;
  public const SALUTATION_MALE = 1;
  public const SALUTATION_FEMALE = 2;

  public const PERSON_TYPE_NATURAL = 0;
  public const PERSON_TYPE_JURIDICAL = 1;

  public const LEVEL_BASE = 0;
  public const LEVEL_BRONZE = 10;
  public const LEVEL_SILVER = 20;
  public const LEVEL_GOLD = 30;

  public const FOUND_VIA_UNKOWN = 0;
  public const FOUND_VIA_RECOMMENDATION = 10;
  public const FOUND_VIA_SOCIALMEDIA = 20;
  public const FOUND_VIA_PRESS = 30;
  public const FOUND_VIA_MISC = 40;

  /**
   * @var ?DateTime
   */
  protected $crdate;

  /**
   * @var ?DateTime
   */
  protected $starttime;

  /**
   * @var ?DateTime
   */
  protected $endtime;

  /**
   * @var bool
   */
  protected $cancellationWish;

  /**
   * @var bool
   */
  protected $reducedRate;

  /**
   * @var int
   */
  protected $state = self::STATE_UNSET;

  /**
   * @Lazy
   *
   * @Cascade("remove")
   */
  protected $feuser;

  /**
   * @var int
   */
  protected $directDebit = 0;

  /**
   * @var ?string
   */
  protected $iban;

  /**
   * @var ?string
   */
  protected $bic;

  /**
   * @var ?string
   */
  protected $account;

  /**
   * @var Location|null
   *
   * @Lazy
   *
   * @Cascade("remove")
   */
  protected $mainLocation;

  /**
   * @var ObjectStorage<Location>
   *
   * @Lazy
   *
   * @Cascade("remove")
   */
  protected $subLocations;

  /**
   * @var ?string
   */
  protected $altBillingName;

  /**
   * @var ?string
   */
  protected $altBillingStreet;

  /**
   * @var ?string
   */
  protected $altBillingZip;

  /**
   * @var ?string
   */
  protected $altBillingCity;

  /**
   * @var Country|null
   *
   * @Lazy
   */
  protected $altBillingCountry;

  /**
   * @var ?string
   */
  protected $ident = '';

  /**
   * @var ?string
   */
  protected $title;

  /**
   * @var ?string
   */
  protected $firstname = '';

  /**
   * @var ?string
   */
  protected $midname = '';

  /**
   * @var ?string
   */
  protected $lastname = '';

  /**
   * @var ?string
   */
  protected $zip = '';

  /**
   * @var ?string
   */
  protected $street = '';

  /**
   * @var ?string
   */
  protected $city = '';

  /**
   * @var int
   */
  protected $federalState;

  /**
   * @var Country
   *
   * @Lazy
   */
  protected $country;

  /**
   * @var ?string
   */
  protected $email;

  /**
   * @var ?string
   */
  protected $phone;

  /**
   * @var ?string
   */
  protected $telefax;

  /**
   * @var ?string
   */
  protected $company;

  /**
   * @var int
   */
  protected $personType;

  /**
   * @var int
   */
  protected $salutation;

  /**
   * @var int
   */
  protected $level;

  /**
   * @var ?string
   */
  protected $addAddressInfo;

  /**
   * @var ?DateTime
   */
  protected $dateofbirth;

  /**
   * @var ?string
   */
  protected $nationality;

  /**
   * @var ?string
   */
  protected $customfield1;

  /**
   * @var ?string
   */
  protected $customfield2;

  /**
   * @var ?string
   */
  protected $customfield3;

  /**
   * @var ?string
   */
  protected $customfield4;

  /**
   * @var ?string
   */
  protected $customfield5;

  /**
   * @var ?string
   */
  protected $customfield6;

  /**
   * @var ?string
   */
  protected $clubFunction;

  /**
   * @var int
   */
  protected $foundVia;

  /**
   * @var ObjectStorage<\Quicko\Clubmanager\Domain\Model\Category>
   *
   * @Lazy
   */
  protected $categories;

  public function __construct()
  {
    $this->categories = new ObjectStorage();
    $this->subLocations = new ObjectStorage();
  }

  public function getIdent(): ?string
  {
    return $this->ident;
  }

  public function setIdent(?string $ident): void
  {
    $this->ident = $ident;
  }

  public function getDateofbirth(): ?DateTime
  {
    return $this->dateofbirth;
  }

  public function setDateofbirth(?DateTime $dateofbirth): void
  {
    $this->dateofbirth = $dateofbirth;
  }

  public function setPersonType(int $personType): void
  {
    $this->personType = $personType;
  }

  public function getPersonType(): int
  {
    return $this->personType;
  }

  public function setSalutation(int $salutation): void
  {
    $this->salutation = $salutation;
  }

  public function getSalutation(): int
  {
    return $this->salutation;
  }

  public function getTitle(): ?string
  {
    return $this->title;
  }

  public function setTitle(?string $title): void
  {
    $this->title = $title;
  }

  public function getFirstname(): ?string
  {
    return $this->firstname;
  }

  public function setFirstname(?string $firstname): void
  {
    $this->firstname = $firstname;
  }

  public function getMidname(): ?string
  {
    return $this->midname;
  }

  public function setMidname(?string $midname): void
  {
    $this->midname = $midname;
  }

  public function getLastname(): ?string
  {
    return $this->lastname;
  }

  public function setLastname(?string $lastname): void
  {
    $this->lastname = $lastname;
  }

  public function getZip(): ?string
  {
    return $this->zip;
  }

  public function setZip(?string $zip): void
  {
    $this->zip = $zip;
  }

  public function getStreet(): ?string
  {
    return $this->street;
  }

  public function setStreet(?string $street): void
  {
    $this->street = $street;
  }

  public function getCity(): ?string
  {
    return $this->city;
  }

  public function setCity(?string $city): void
  {
    $this->city = $city;
  }

  public function getNationality(): ?string
  {
    return $this->nationality;
  }

  public function setNationality(?string $nationality): void
  {
    $this->nationality = $nationality;
  }

  public function getCountry(): ?Country
  {
    /* @phpstan-ignore-next-line */
    if ($this->country instanceof LazyLoadingProxy) {
      /** @var Country|null $resolvedValue */
      $resolvedValue = $this->country->_loadRealInstance();

      return $this->country = $resolvedValue instanceof Country
          ? $resolvedValue
          : null;
    }

    return $this->country;
  }

  public function setCountry(Country $country): void
  {
    $this->country = $country;
  }

  public function getFederalState(): int
  {
    return $this->federalState;
  }

  public function setFederalState(int $federalState): void
  {
    $this->federalState = $federalState;
  }

  public function getFederalStateName(): ?string
  {
    $states = States::getStates();
    foreach ($states as $stateArray) {
      if ($stateArray[1] == $this->federalState) {
        return $stateArray[0];
      }
    }

    return '';
  }

  public function getCompany(): ?string
  {
    return $this->company;
  }

  public function setCompany(?string $company): void
  {
    $this->company = $company;
  }

  public function getEmail(): ?string
  {
    return $this->email;
  }

  public function setEmail(?string $email): void
  {
    $this->email = $email;
  }

  public function getPhone(): ?string
  {
    return $this->phone;
  }

  public function setPhone(?string $phone): void
  {
    $this->phone = $phone;
  }

  public function getTelefax(): ?string
  {
    return $this->telefax;
  }

  public function setTelefax(?string $telefax): void
  {
    $this->telefax = $telefax;
  }

  public function getIban(): ?string
  {
    return $this->iban;
  }

  public function setIban(?string $iban): void
  {
    $this->iban = $iban;
  }

  public function getBic(): ?string
  {
    return $this->bic;
  }

  public function setBic(?string $bic): void
  {
    $this->bic = $bic;
  }

  public function getAccount(): ?string
  {
    return $this->account;
  }

  public function setAccount(?string $account): void
  {
    $this->account = $account;
  }

  public function getMainLocation(): ?Location
  {
    /* @phpstan-ignore-next-line */
    if ($this->mainLocation instanceof LazyLoadingProxy) {
      /** @var Location|null $resolvedValue */
      $resolvedValue = $this->mainLocation->_loadRealInstance();

      return $this->mainLocation = $resolvedValue instanceof Location
          ? $resolvedValue
          : null;
    }

    return $this->mainLocation;
  }

  /**
   * @param ?Location $mainLocation
   *
   * @return void
   */
  public function setMainLocation($mainLocation)
  {
    $this->mainLocation = $mainLocation;
  }

  /**
   * @return ObjectStorage<Location>
   */
  public function getSubLocations()
  {
    return $this->subLocations;
  }

  /**
   * @param ObjectStorage<Location> $subLocations
   */
  public function setSubLocations($subLocations): void
  {
    $this->subLocations = $subLocations;
  }

  public function getAltBillingName(): ?string
  {
    return $this->altBillingName;
  }

  public function setAltBillingName(?string $altBillingName): void
  {
    $this->altBillingName = $altBillingName;
  }

  public function getAltBillingStreet(): ?string
  {
    return $this->altBillingStreet;
  }

  public function setAltBillingStreet(?string $altBillingStreet): void
  {
    $this->altBillingStreet = $altBillingStreet;
  }

  public function getAltBillingZip(): ?string
  {
    return $this->altBillingZip;
  }

  public function setAltBillingZip(?string $altBillingZip): void
  {
    $this->altBillingZip = $altBillingZip;
  }

  public function getAltBillingCity(): ?string
  {
    return $this->altBillingCity;
  }

  public function setAltBillingCity(?string $altBillingCity): void
  {
    $this->altBillingCity = $altBillingCity;
  }

  public function getAltBillingCountry(): ?Country
  {
    /* @phpstan-ignore-next-line */
    if ($this->altBillingCountry instanceof LazyLoadingProxy) {
      /** @var Country|null $resolvedValue */
      $resolvedValue = $this->altBillingCountry->_loadRealInstance();

      return $this->altBillingCountry = $resolvedValue instanceof Country
          ? $resolvedValue
          : null;
    }

    return $this->altBillingCountry;
  }

  /**
   * @param ?Country $altBillingCountry
   */
  public function setAltBillingCountry($altBillingCountry): void
  {
    $this->altBillingCountry = $altBillingCountry;
  }

  public function getFeuser(): ?FrontendUser
  {
    if ($this->feuser instanceof LazyLoadingProxy) {
      /** @var FrontendUser|null $resolvedValue */
      $resolvedValue = $this->feuser->_loadRealInstance();

      return $this->feuser = $resolvedValue instanceof FrontendUser
          ? $resolvedValue
          : null;
    }

    return $this->feuser;
  }

  public function setFeuser(FrontendUser $feuser): void
  {
    $this->feuser = $feuser;
  }

  public function setState(int $state): void
  {
    $this->state = $state;
  }

  public function getState(): int
  {
    return $this->state;
  }

  public function hasState(int $state): bool
  {
    return $this->state === $state;
  }

  /**
   * @return array<LazyLoadingProxy|Location|null>
   */
  public function getAllLocations()
  {
    $result = [];
    if ($this->mainLocation) {
      $result[] = $this->mainLocation;
    }
    if ($this->subLocations != null) {
      foreach ($this->subLocations as $location) {
        $result[] = $location;
      }
    }

    return $result;
  }

  /**
   * @return ObjectStorage<\Quicko\Clubmanager\Domain\Model\Category>
   */
  public function getCategories(): ObjectStorage
  {
    return $this->categories;
  }

  /**
   * @param ObjectStorage<\Quicko\Clubmanager\Domain\Model\Category> $categories
   */
  public function setCategories(ObjectStorage $categories): void
  {
    $this->categories = $categories;
  }

  public function setLevel(int $level): void
  {
    $this->level = $level;
  }

  public function getLevel(): int
  {
    return $this->level;
  }

  public function setAddAddressInfo(?string $addAddressInfo): void
  {
    $this->addAddressInfo = $addAddressInfo;
  }

  public function getAddAddressInfo(): ?string
  {
    return $this->addAddressInfo;
  }

  public function getCrdate(): ?DateTime
  {
    return $this->crdate;
  }

  public function getStarttime(): ?DateTime
  {
    return $this->starttime;
  }

  public function setStarttime(DateTime $starttime): void
  {
    $this->starttime = $starttime;
  }

  public function getEndtime(): ?DateTime
  {
    return $this->endtime;
  }

  public function setEndtime(?DateTime $endtime): void
  {
    $this->endtime = $endtime;
  }

  public function getCancellationWish(): bool
  {
    return $this->cancellationWish;
  }

  public function setCancellationWish(bool $cancellationWish): void
  {
    $this->cancellationWish = $cancellationWish;
  }

  public function getReducedRate(): bool
  {
    return $this->reducedRate;
  }

  public function setReducedRate(bool $reducedRate): void
  {
    $this->reducedRate = $reducedRate;
  }

  public function getDirectDebit(): int
  {
    return $this->directDebit;
  }

  public function setDirectDebit(int $directDebit): void
  {
    $this->directDebit = $directDebit;
  }

  public function getCustomfield1(): ?string
  {
    return $this->customfield1;
  }

  public function setCustomfield1(?string $customfield1): void
  {
    $this->customfield1 = $customfield1;
  }

  public function getCustomfield2(): ?string
  {
    return $this->customfield2;
  }

  public function setCustomfield2(?string $customfield2): void
  {
    $this->customfield2 = $customfield2;
  }

  public function getCustomfield3(): ?string
  {
    return $this->customfield3;
  }

  public function setCustomfield3(?string $customfield3): void
  {
    $this->customfield3 = $customfield3;
  }

  public function getCustomfield4(): ?string
  {
    return $this->customfield4;
  }

  public function setCustomfield4(?string $customfield4): void
  {
    $this->customfield4 = $customfield4;
  }

  public function getCustomfield5(): ?string
  {
    return $this->customfield5;
  }

  public function setCustomfield5(?string $customfield5): void
  {
    $this->customfield5 = $customfield5;
  }

  public function getCustomfield6(): ?string
  {
    return $this->customfield6;
  }

  public function setCustomfield6(?string $customfield6): void
  {
    $this->customfield6 = $customfield6;
  }

  public function getClubFunction(): ?string
  {
    return $this->clubFunction;
  }

  public function setClubFunction(?string $clubFunction): void
  {
    $this->clubFunction = $clubFunction;
  }

  public function setFoundVia(int $foundVia): void
  {
    $this->foundVia = $foundVia;
  }

  public function getFoundVia(): int
  {
    return $this->foundVia;
  }

  /**
   * @param array<mixed> $arrayHaystack
   * @param array<mixed> $arrayNeedles
   */
  private static function arrayContainsAnyOf($arrayHaystack, $arrayNeedles): bool
  {
    foreach ($arrayNeedles as $needle) {
      if (in_array($needle, $arrayHaystack)) {
        return true;
      }
    }

    return false;
  }

  /**
   * @param array<mixed> $categoryList
   */
  public function hasAnyCategory($categoryList): bool
  {
    return self::arrayContainsAnyOf(
      $this->categories->toArray(),
      $categoryList
    );
  }

  /**
   * @param array<mixed> $categoryList
   */
  public function anyLocationHasAnyCategory($categoryList): bool
  {
    $categories = [];
    if ($this->mainLocation) {
      $categories = array_merge($categories, $this->mainLocation->getCategories()->toArray());
    }
    foreach ($this->subLocations as $subLocation) {
      $categories = array_merge($categories, $subLocation->getCategories()->toArray());
    }

    return self::arrayContainsAnyOf(
      $categories,
      $categoryList
    );
  }

  public function buildHtmlAddress(): string
  {
    $result = '';

    if (!empty($this->getTitle())) {
      $result = "{$this->getTitle()} ";
    }
    $country = '';
    if ($this->getCountry()) {
      $country = '<br>' . $this->getCountry()->getShortNameLocal();
    }
    $result .= "{$this->getFirstname()} {$this->getLastname()}<br>"
            . "{$this->getStreet()}<br>"
            . "{$this->getZip()} {$this->getCity()}"
            . $country
    ;

    return $result;
  }

  public function buildHtmlInvoiceAdress(): string
  {
    if (!empty($this->getAltBillingName())) {
      return "{$this->getAltBillingName()}<br>"
      . "{$this->getAltBillingStreet()}<br>"
      . "{$this->getAltBillingZip()} {$this->getAltBillingCity()}<br>"
      . "{$this->getAltBillingCountry()->getShortNameLocal()}"
      ;
    } else {
      return $this->buildHtmlAddress();
    }
  }

  public function buildSalutation(): string
  {
    $fullMemberName = '';
    $title = $this->getTitle();
    if (!empty($title)) {
      $fullMemberName = $title;
    }
    $fullMemberName .= $this->getFirstname() . '  ' . $this->getLastname();
    $translationKey = 'LLL:EXT:clubmanager/Resources/Private/Language/locallang.xlf:member.salutation.' . $this->getSalutation();

    return LocalizationUtility::translate($translationKey, 'clubmanager', [$fullMemberName]);
  }
}
