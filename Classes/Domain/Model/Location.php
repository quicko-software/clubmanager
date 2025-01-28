<?php

namespace Quicko\Clubmanager\Domain\Model;

use DateTime;
use Quicko\Clubmanager\Domain\Helper\States;
use TYPO3\CMS\Extbase\Annotation\ORM\Cascade;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class Location extends AbstractEntity
{
  protected DateTime $tstamp;

  protected bool $hidden;

  protected Member $member;

  protected string $slug;

  protected int $kind;

  protected int $salutation;

  protected string $title;

  protected string $firstname;

  protected string $midname;

  protected string $lastname;

  protected string $company;

  protected string $street;

  protected string $addAddressInfo;

  protected string $zip;

  protected string $city;

  protected int $state;

  protected Country $country;

  protected string $latitude;

  protected string $longitude;

  /**
   * @Cascade("remove")
   */
  protected FileReference $image;

  protected string $info;

  /**
   * @var ObjectStorage<Category>
   *
   * @Lazy
   */
  protected $categories;

  protected string $phone;

  protected string $mobile;

  protected string $fax;

  protected string $email;

  protected string $website;

  /**
   * @var ObjectStorage<\Quicko\Clubmanager\Domain\Model\Socialmedia>
   *
   * @Lazy
   *
   * @Cascade("remove")
   */
  protected $socialmedia;

  protected string $youtubeVideo;

  public function __construct()
  {
    $this->initStorageObjects();
  }

  protected function initStorageObjects(): void
  {
    $this->socialmedia = new ObjectStorage();
    $this->categories = new ObjectStorage();
  }

  public function getAddAddressInfo(): string
  {
    return $this->addAddressInfo;
  }

  public function setAddAddressInfo(string $addAddressInfo): void
  {
    $this->addAddressInfo = $addAddressInfo;
  }

  public function getKind(): int
  {
    return $this->kind;
  }

  public function setKind(int $kind): void
  {
    $this->kind = $kind;
  }

  public function getMember(): Member
  {
    return $this->member;
  }

  public function setMember(Member $member): void
  {
    $this->member = $member;
  }

  public function getSalutation(): int
  {
    return $this->salutation;
  }

  public function setSalutation(int $salutation): void
  {
    $this->salutation = $salutation;
  }

  public function getSlug(): string
  {
    return $this->slug;
  }

  public function setSlug(string $slug): void
  {
    $this->slug = $slug;
  }

  public function getTitle(): string
  {
    return $this->title;
  }

  public function setTitle(string $title): void
  {
    $this->title = $title;
  }

  public function getFirstname(): string
  {
    return $this->firstname;
  }

  public function setFirstname(string $firstname): void
  {
    $this->firstname = $firstname;
  }

  public function getMidname(): string
  {
    return $this->midname;
  }

  public function setMidname(string $midname): void
  {
    $this->midname = $midname;
  }

  public function getLastname(): string
  {
    return $this->lastname;
  }

  public function setLastname(string $lastname): void
  {
    $this->lastname = $lastname;
  }

  public function getInfo(): string
  {
    return $this->info;
  }

  public function setInfo(string $info): void
  {
    $this->info = $info;
  }

  public function getCompany(): string
  {
    return $this->company;
  }

  public function setCompany(string $company): void
  {
    $this->company = $company;
  }

  public function getStreet(): string
  {
    return $this->street;
  }

  public function setStreet(string $street): void
  {
    $this->street = $street;
  }

  public function getZip(): string
  {
    return $this->zip;
  }

  public function setZip(string $zip): void
  {
    $this->zip = $zip;
  }

  public function getCity(): string
  {
    return $this->city;
  }

  public function setCity(string $city): void
  {
    $this->city = $city;
  }

  public function getLatitude(): string
  {
    return $this->latitude;
  }

  public function setLatitude(string $latitude): void
  {
    $this->latitude = $latitude;
  }

  public function getLongitude(): string
  {
    return $this->longitude;
  }

  public function setLongitude(string $longitude): void
  {
    $this->longitude = $longitude;
  }

  public function getImage(): ?FileReference
  {
    return $this->image;
  }

  public function setImage(?FileReference $image): void
  {
    $this->image = $image;
  }

  public function getPhone(): string
  {
    return $this->phone;
  }

  public function setPhone(string $phone): void
  {
    $this->phone = $phone;
  }

  public function getMobile(): string
  {
    return $this->mobile;
  }

  public function setMobile(string $mobile): void
  {
    $this->mobile = $mobile;
  }

  public function getFax(): string
  {
    return $this->fax;
  }

  public function setFax(string $fax): void
  {
    $this->fax = $fax;
  }

  public function getEmail(): string
  {
    return $this->email;
  }

  public function setEmail(string $email): void
  {
    $this->email = $email;
  }

  public function getWebsite(): string
  {
    return $this->website;
  }

  public function setWebsite(string $website): void
  {
    $this->website = $website;
  }

  /**
   * @return ObjectStorage<Socialmedia>
   */
  public function getSocialmedia(): ObjectStorage
  {
    return $this->socialmedia;
  }

  /**
   * @param ObjectStorage<\Quicko\Clubmanager\Domain\Model\Socialmedia> $socialmedia
   */
  public function setSocialmedia(ObjectStorage $socialmedia): void
  {
    $this->socialmedia = $socialmedia;
  }

  public function getYoutubeVideo(): string
  {
    return $this->youtubeVideo;
  }

  public function setYoutubeVideo(string $youtubeVideo): void
  {
    $this->youtubeVideo = $youtubeVideo;
  }

  /**
   * Summary of getCategories.
   *
   * @return ObjectStorage<Category>
   */
  public function getCategories(): ObjectStorage
  {
    return $this->categories;
  }

  /**
   * @param ObjectStorage<Category> $categories
   */
  public function setCategories(ObjectStorage $categories): void
  {
    $this->categories = $categories;
  }

  public function getCountry(): Country|null
  {
    return $this->country;
  }

  public function setCountry(Country $country): void
  {
    $this->country = $country;
  }

  public function getState(): int
  {
    return $this->state;
  }

  public function setState(int $state): void
  {
    $this->state = $state;
  }

  public function getStateName(): string
  {
    $states = States::getStates();
    foreach ($states as $stateArray) {
      if ($stateArray['value'] == $this->state) {
        return $stateArray['label'];
      }
    }

    return '';
  }

  public function getTstamp(): DateTime
  {
    return $this->tstamp;
  }

  public function setTstamp(DateTime $tstamp): void
  {
    $this->tstamp = $tstamp;
  }

  public function getHidden(): bool
  {
    return $this->hidden;
  }

  public function setHidden(bool $hidden): void
  {
    $this->hidden = $hidden;
  }
}
