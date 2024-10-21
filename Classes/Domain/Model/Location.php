<?php

namespace Quicko\Clubmanager\Domain\Model;

use TYPO3\CMS\Extbase\Annotation\ORM\Cascade;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy;

use Quicko\Clubmanager\Domain\Model\Country;
use Quicko\Clubmanager\Domain\Helper\States;

class Location extends AbstractEntity
{

  /**
   * @var \DateTime
   */
  protected $tstamp;


  /**
   * @var bool
   */
  protected $hidden;

  /**
   * @var \Quicko\Clubmanager\Domain\Model\Member
   */
  protected $member;

  /**
   * @var \string
   */
  protected $slug;

  /**
   * @var \integer
   */
  protected $kind;

  /**
   * @var int
   */
  protected $salutation;

  /**
   * @var \string
   */
  protected $title;

  /**
   * @var \string
   */
  protected $firstname;

  /**
   * @var \string
   */
  protected $midname;

  /**
   * @var \string
   */
  protected $lastname;


  /**
   * @var \string
   */
  protected $company;

  /**
   * @var \string
   */
  protected $street;

  /**
   * @var string
   */
  protected $addAddressInfo;

  /**
   * @var \string
   */
  protected $zip;

  /**
   * @var \string
   */
  protected $city;

  /**
   * @var \int
   */
  protected $state;

  /**
   * @var \Quicko\Clubmanager\Domain\Model\Country
   * @Lazy
   */
  protected $country;

  /**
   * @var \string
   */
  protected $latitude;

  /**
   * @var \string
   */
  protected $longitude;

  /**
   * @var FileReference
   * @Cascade("remove")
   */
  protected $image = null;

  /**
   * @var \string
   */
  protected $info;

  /**
   * @var ObjectStorage<\Quicko\Clubmanager\Domain\Model\Category>
   * @Lazy
   */
  protected $categories;

  /**
   * @var \string
   */
  protected $phone;

  /**
   * @var \string
   */
  protected $mobile;  

  /**
   * @var \string
   */
  protected $fax;    

  /**
   * @var \string
   */
  protected $email;     
  
  /**
   * @var \string
   */
  protected $website;    

  /**
   * @var ObjectStorage<\Quicko\Clubmanager\Domain\Model\Socialmedia>
   * @Lazy
   * @Cascade("remove")
   */
  protected $socialmedia;

  /**
   * @var \string
   */
  protected $youtubeVideo;

  public function __construct()
  {
    $this->initStorageObjects();
  }

  protected function initStorageObjects()
  {
    $this->socialmedia = new ObjectStorage();
    $this->categories = new ObjectStorage();
  }

  public function getAddAddressInfo()
  {
    return $this->addAddressInfo;
  }

  public function setAddAddressInfo($addAddressInfo)
  {
    $this->addAddressInfo = $addAddressInfo;
  }

  public function getKind()
  {
    return $this->kind;
  }

  public function setKind($kind)
  {
    $this->kind = $kind;
  }

  public function getMember()
  {
    return $this->member;
  }

  public function setMember(\Quicko\Clubmanager\Domain\Model\Member $member)
  {
    $this->member = $member;
  }

  public function getSalutation(): int
  {
    return $this->salutation;
  }

  public function setSalutation($salutation): void
  {
    $this->salutation = $salutation;
  }

  public function getSlug()
  {
    return $this->slug;
  }

  public function setSlug($slug)
  {
    $this->slug = $slug;
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

  public function getInfo()
  {
    return $this->info;
  }

  public function setInfo($info)
  {
    $this->info = $info;
  }

  public function getCompany()
  {
    return $this->company;
  }

  public function setCompany($company)
  {
    $this->company = $company;
  }

  public function getStreet()
  {
    return $this->street;
  }

  public function setStreet($street)
  {
    $this->street = $street;
  }

  public function getZip()
  {
    return $this->zip;
  }

  public function setZip($zip)
  {
    $this->zip = $zip;
  }

  public function getCity()
  {
    return $this->city;
  }

  public function setCity($city)
  {
    $this->city = $city;
  }

  public function getLatitude()
  {
    return $this->latitude;
  }

  public function setLatitude($latitude)
  {
    $this->latitude = $latitude;
  }

  public function getLongitude()
  {
    return $this->longitude;
  }

  public function setLongitude($longitude)
  {
    $this->longitude = $longitude;
  }

  public function getImage()
  {
    return $this->image;
  }

  public function setImage($image)
  {
    $this->image = $image;
  }

  public function getPhone()
  {
    return $this->phone;
  }

  public function setPhone($phone)
  {
    $this->phone = $phone;
  }

  public function getMobile()
  {
    return $this->mobile;
  }

  public function setMobile($mobile)
  {
    $this->mobile = $mobile;
  }  

  public function getFax()
  {
    return $this->fax;
  }

  public function setFax($fax)
  {
    $this->fax = $fax;
  }  
  
  public function getEmail()
  {
    return $this->email;
  }

  public function setEmail($email)
  {
    $this->email = $email;
  }  

  public function getWebsite()
  {
    return $this->website;
  }

  public function setWebsite($website)
  {
    $this->website = $website;
  }    

  public function getSocialmedia()
  {
    return $this->socialmedia;
  }

  public function setSocialmedia(ObjectStorage $socialmedia)
  {
    $this->socialmedia = $socialmedia;
  }

  public function getYoutubeVideo()
  {
    return $this->youtubeVideo;
  }

  public function setYoutubeVideo($youtubeVideo)
  {
    $this->youtubeVideo = $youtubeVideo;
  }
  
  public function getCategories()
  {
    return $this->categories;
  }

  public function setCategories(ObjectStorage $categories)
  {
    $this->categories = $categories;
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

  public function setCountry(\Quicko\Clubmanager\Domain\Model\Country $country)
  {
    $this->country = $country;
  }

  public function getState()
  {
    return $this->state;
  }

  public function setState($state)
  {
    $this->state = $state;
  }

  public function getStateName() : string {
    $states = States::getStates();
    foreach($states as $stateArray) {
      if($stateArray[1] == $this->state) {
        return $stateArray[0];
      }
    }
    return "";
  }

  public function getTstamp()
  {
    return $this->tstamp;
  }

  public function setTstamp($tstamp)
  {
    $this->tstamp = $tstamp;
  }

  public function getHidden()
  {
    return $this->hidden;
  }

  public function setHidden($hidden)
  {
    $this->hidden = $hidden;
  }
}
