<?php

namespace Quicko\Clubmanager\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Annotation\ORM\Cascade;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy;

use Quicko\Clubmanager\Domain\Model\FrontendUser;
use Quicko\Clubmanager\Domain\Model\Country;
use Quicko\Clubmanager\Domain\Model\Location;
use Quicko\Clubmanager\Domain\Helper\States;

class Member extends AbstractEntity
{
    const STATE_UNSET = 0;
    const STATE_APPLIED = 1;
    const STATE_ACTIVE = 2;
    const STATE_SUSPENDED = 3;
    const STATE_CANCELLED = 4;

    const SALUTATION_OTHER = 0;
    const SALUTATION_MALE = 1;
    const SALUTATION_FEMALE = 2;

    const PERSON_TYPE_NATURAL = 0;
    const PERSON_TYPE_JURIDICAL = 1;

    const LEVEL_BASE = 0;
    const LEVEL_BRONZE = 10;
    const LEVEL_SILVER = 20;
    const LEVEL_GOLD = 30;


    /**
     * @var \DateTime
     */
    protected $crdate = null;

    /**
     *
     * @var \DateTime
     */
    protected $starttime;

    /**
     *
     * @var \DateTime
     */
    protected $endtime;

    /**
     *
     * @var boolean
     */
    protected $cancellationWish;

    /**
     *
     * @var boolean
     */
    protected $reducedRate;

    /**
     * @var \integer
     */
    protected $state = self::STATE_UNSET;

    /**
     * @var \Quicko\Clubmanager\Domain\Model\FrontendUser
     * @Lazy
     * @Cascade("remove")
     */
    protected $feuser;

    /**
     * @var \integer
     */
    protected $directDebit = 0;

    /**
     * @var \string
     */
    protected $iban;

    /**
     * @var \string
     */
    protected $bic;

    /**
     * @var \string
     */
    protected $account;

    /**
     * @var ?Location
     * @Lazy
     * @Cascade("remove")
     */
    protected $mainLocation;

    /**
     * @var ObjectStorage<Location>
     * @Lazy
     * @Cascade("remove")
     */
    protected $subLocations;

    /**
     * @var string
     */
    protected $altBillingName;

    /**
     * @var string
     */
    protected $altBillingStreet;

    /**
     * @var string
     */
    protected $altBillingZip;

    /**
     * @var string
     */
    protected $altBillingCity;

    /**
     * @var Country
     * @Lazy
     */
    protected $altBillingCountry;

    /**
     * @var \string
     */
    protected $ident = '';

    /**
     * @var \string
     */
    protected $title;

    /**
     * @var \string
     */
    protected $firstname = '';

    /**
     * @var \string
     */
    protected $midname = '';

    /**
     * @var \string
     */
    protected $lastname = '';

    /**
     * @var \string
     */
    protected $zip = '';

    /**
     * @var \string
     */
    protected $street = '';

    /**
     * @var \string
     */
    protected $city = '';

    /**
     * @var \int
     */
    protected $federalState;

    /**
     * @var Country
     * @Lazy
     */
    protected $country;

    /**
     * @var \string
     */
    protected $email;

    /**
     * @var \string
     */
    protected $phone;

    /**
     * @var \string
     */
    protected $telefax;

    /**
     * @var \string
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
     * @var \string
     */
    protected $addAddressInfo;

    /**
     * @var \DateTime
     */
    protected $dateofbirth;

    /**
     * @var \string
     */
    protected $nationality;

    /**
     * @var \string
     */
    protected $customfield1;

    /**
     * @var \string
     */
    protected $customfield2;

    /**
     * @var \string
     */
    protected $customfield3;

    /**
     * @var \string
     */
    protected $customfield4;

    /**
     * @var \string
     */
    protected $customfield5;

    /**
     * @var \string
     */
    protected $customfield6;

    /**
     * @var ObjectStorage<\Quicko\Clubmanager\Domain\Model\Category>
     * @Lazy
     */
    protected $categories;

    public function __construct()
    {
        $this->categories = new ObjectStorage();
        $this->subLocations = new ObjectStorage();
    }

    public function getIdent()
    {
        return $this->ident;
    }

    public function setIdent($ident)
    {
        $this->ident = $ident;
    }

    public function getDateofbirth()
    {
        return $this->dateofbirth;
    }

    public function setDateofbirth($dateofbirth)
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

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getFirstname()
    {
        return $this->firstname;
    }

    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    }

    public function getMidname()
    {
        return $this->midname;
    }

    public function setMidname($midname)
    {
        $this->midname = $midname;
    }

    public function getLastname()
    {
        return $this->lastname;
    }

    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    }

    public function getZip()
    {
        return $this->zip;
    }

    public function setZip($zip)
    {
        $this->zip = $zip;
    }

    public function getStreet()
    {
        return $this->street;
    }

    public function setStreet($street)
    {
        $this->street = $street;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function setCity($city)
    {
        $this->city = $city;
    }

    public function getNationality()
    {
        return $this->nationality;
    }

    public function setNationality($nationality)
    {
        $this->nationality = $nationality;
    }

    public function getCountry()
    {
        if ($this->country instanceof LazyLoadingProxy) {
            /** @var Country|null $resolvedValue */
            $resolvedValue = $this->country->_loadRealInstance();
            return $this->country = $resolvedValue instanceof Country
                ? $resolvedValue
                : null;
        }
        return $this->country;
    }

    public function setCountry(Country $country)
    {
        $this->country = $country;
    }

    public function getFederalState()
    {
        return $this->federalState;
    }

    public function setFederalState($federalState)
    {
        $this->federalState = $federalState;
    }

    public function getFederalStateName() : string {
        $states = States::getStates();
        foreach($states as $stateArray) {
            if($stateArray[1] == $this->federalState) {
                return $stateArray[0];
            }
        }
        return "";
    }

    public function getCompany()
    {
        return $this->company;
    }

    public function setCompany($company)
    {
        $this->company = $company;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    public function getTelefax()
    {
        return $this->telefax;
    }

    public function setTelefax($telefax)
    {
        $this->telefax = $telefax;
    }

    public function getIban()
    {
        return $this->iban;
    }

    public function setIban($iban)
    {
        $this->iban = $iban;
    }

    public function getBic()
    {
        return $this->bic;
    }

    public function setBic($bic)
    {
        $this->bic = $bic;
    }

    public function getAccount()
    {
        return $this->account;
    }

    public function setAccount($account)
    {
        $this->account = $account;
    }

    /**
     * @return \Quicko\Clubmanager\Domain\Model\Location
     */
    public function getMainLocation(): ?Location
    {
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
     * @return void
     */
    public function setMainLocation($mainLocation)
    {
        $this->mainLocation = $mainLocation;
    }

    public function getSubLocations()
    {
        return $this->subLocations;
    }

    public function setSubLocations($subLocations)
    {
        $this->subLocations = $subLocations;
    }

    public function getAltBillingName()
    {
        return $this->altBillingName;
    }

    public function setAltBillingName($altBillingName)
    {
        $this->altBillingName = $altBillingName;
    }

    public function getAltBillingStreet()
    {
        return $this->altBillingStreet;
    }

    public function setAltBillingStreet($altBillingStreet)
    {
        $this->altBillingStreet = $altBillingStreet;
    }

    public function getAltBillingZip()
    {
        return $this->altBillingZip;
    }

    public function setAltBillingZip($altBillingZip)
    {
        $this->altBillingZip = $altBillingZip;
    }

    public function getAltBillingCity()
    {
        return $this->altBillingCity;
    }

    public function setAltBillingCity($altBillingCity)
    {
        $this->altBillingCity = $altBillingCity;
    }

    public function getAltBillingCountry()
    {
        if ($this->altBillingCountry instanceof LazyLoadingProxy) {
            /** @var Country|null $resolvedValue */
            $resolvedValue = $this->altBillingCountry->_loadRealInstance();
            return $this->altBillingCountry = $resolvedValue instanceof Country
                ? $resolvedValue
                : null;
        }
        return $this->altBillingCountry;
    }

    public function setAltBillingCountry($altBillingCountry)
    {
        $this->altBillingCountry = $altBillingCountry;
    }

    public function getFeuser()
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

    public function setFeuser($feuser)
    {
        $this->feuser = $feuser;
    }

    public function setState($state)
    {
        $this->state = $state;
    }

    public function getState()
    {
        return $this->state;
    }

    public function hasState(int $state): bool
    {
        return $this->state === $state;
    }

    public function getAllLocations()
    {
        $result = [];
        if ($this->mainLocation) {
            $result[] = $this->mainLocation;
        }
        if ($this->subLocations) {
            foreach ($this->subLocations as $location) {
                $result[] = $location;
            }
        }
        return $result;
    }

    public function getCategories(): ObjectStorage
    {
        return $this->categories;
    }

    public function setCategories(ObjectStorage $categories): void
    {
        $this->categories = $categories;
    }

    public function setLevel($level)
    {
        $this->level = $level;
    }

    public function getLevel()
    {
        return $this->level;
    }

    public function setAddAddressInfo($addAddressInfo)
    {
        $this->addAddressInfo = $addAddressInfo;
    }

    public function getAddAddressInfo()
    {
        return $this->addAddressInfo;
    }

    public function getCrdate()
    {
        return $this->crdate;
    }

    public function getStarttime()
    {
        return $this->starttime;
    }

    public function setStarttime($starttime)
    {
        $this->starttime = $starttime;
    }

    public function getEndtime()
    {
        return $this->endtime;
    }

    public function setEndtime($endtime)
    {
        $this->endtime = $endtime;
    }

    public function getCancellationWish()
    {
        return $this->cancellationWish;
    }

    public function setCancellationWish($cancellationWish)
    {
        $this->cancellationWish = $cancellationWish;
    }

    public function getReducedRate()
    {
        return $this->reducedRate;
    }

    public function setReducedRate($reducedRate)
    {
        $this->reducedRate = $reducedRate;
    }

    public function getDirectDebit()
    {
        return $this->directDebit;
    }

    public function setDirectDebit($directDebit)
    {
        $this->directDebit = $directDebit;
    }

    public function getCustomfield1()
    {
        return $this->customfield1;
    }

    public function setCustomfield1($customfield1)
    {
        $this->customfield1 = $customfield1;
    }

    public function getCustomfield2()
    {
        return $this->customfield2;
    }

    public function setCustomfield2($customfield2)
    {
        $this->customfield2 = $customfield2;
    }

    public function getCustomfield3()
    {
        return $this->customfield3;
    }

    public function setCustomfield3($customfield3)
    {
        $this->customfield3 = $customfield3;
    }

    public function getCustomfield4()
    {
        return $this->customfield4;
    }

    public function setCustomfield4($customfield4)
    {
        $this->customfield4 = $customfield4;
    }

    public function getCustomfield5()
    {
        return $this->customfield5;
    }

    public function setCustomfield5($customfield5)
    {
        $this->customfield5 = $customfield5;
    }

    public function getCustomfield6()
    {
        return $this->customfield6;
    }

    public function setCustomfield6($customfield6)
    {
        $this->customfield6 = $customfield6;
    }


    private static function arrayContainsAnyOf($arrayHaystack, $arrayNeedles): bool
    {
        foreach ($arrayNeedles as $needle) {
            if (in_array($needle, $arrayHaystack)) {
                return true;
            }
        }
        return false;
    }

    public function hasAnyCategory($categoryList): bool
    {
        return self::arrayContainsAnyOf(
            $this->categories->toArray(),
            $categoryList
        );
    }

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
}
