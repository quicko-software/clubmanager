<?php

namespace Quicko\Clubmanager\Domain\Model;

use DateTime;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class FrontendUser extends AbstractEntity
{
  protected bool $hidden = true;

  protected int $lastreminderemailsent = 0;

  protected string $username = '';

  protected string $password = '';

  /**
   * @var ObjectStorage<FrontendUserGroup>
   */
  protected ObjectStorage $usergroup;

  protected string $name = '';

  protected string $firstName = '';

  protected string $middleName = '';

  protected string $lastName = '';

  protected string $address = '';

  protected string $telephone = '';

  protected string $fax = '';

  protected string $email = '';

  protected string $lockToDomain = '';

  protected string $title = '';

  protected string $zip = '';

  protected string $city = '';

  protected string $country = '';

  protected string $www = '';

  protected string $company = '';

  /**
   * @var ObjectStorage<FileReference>
   */
  protected $image;

  protected ?DateTime $lastlogin;

  public function __construct(string $username = '', string $password = '')
  {
    $this->username = $username;
    $this->password = $password;
    $this->usergroup = new ObjectStorage();
    $this->image = new ObjectStorage();
  }

  /**
   * Called again with initialize object, as fetching an entity from the DB does not use the constructor.
   */
  public function initializeObject(): void
  {
    $this->usergroup = new ObjectStorage();
    $this->image = new ObjectStorage();
  }

  public function setUsername(string $username): void
  {
    $this->username = $username;
  }

  public function getUsername(): string
  {
    return $this->username;
  }

  public function setPassword(string $password): void
  {
    $this->password = $password;
  }

  public function getPassword(): string
  {
    return $this->password;
  }

  /**
   * @param ObjectStorage<FrontendUserGroup> $usergroup
   */
  public function setUsergroup(ObjectStorage $usergroup): void
  {
    $this->usergroup = $usergroup;
  }

  public function addUsergroup(FrontendUserGroup $usergroup): void
  {
    $this->usergroup->attach($usergroup);
  }

  /**
   * Removes a usergroup from the frontend user.
   */
  public function removeUsergroup(FrontendUserGroup $usergroup): void
  {
    $this->usergroup->detach($usergroup);
  }

  /**
   * Returns the usergroups. Keep in mind that the property is called "usergroup"
   * although it can hold several usergroups.
   *
   * @return ObjectStorage<FrontendUserGroup> An object storage containing the usergroup
   */
  public function getUsergroup(): ObjectStorage
  {
    return $this->usergroup;
  }

  public function setName(string $name): void
  {
    $this->name = $name;
  }

  public function getName(): string
  {
    return $this->name;
  }

  public function setFirstName(string $firstName): void
  {
    $this->firstName = $firstName;
  }

  public function getFirstName(): string
  {
    return $this->firstName;
  }

  public function setMiddleName(string $middleName): void
  {
    $this->middleName = $middleName;
  }

  public function getMiddleName(): string
  {
    return $this->middleName;
  }

  public function setLastName(string $lastName): void
  {
    $this->lastName = $lastName;
  }

  public function getLastName(): string
  {
    return $this->lastName;
  }

  public function setAddress(string $address): void
  {
    $this->address = $address;
  }

  public function getAddress(): string
  {
    return $this->address;
  }

  public function setTelephone(string $telephone): void
  {
    $this->telephone = $telephone;
  }

  public function getTelephone(): string
  {
    return $this->telephone;
  }

  public function setFax(string $fax): void
  {
    $this->fax = $fax;
  }

  public function getFax(): string
  {
    return $this->fax;
  }

  public function setEmail(string $email): void
  {
    $this->email = $email;
  }

  public function getEmail(): string
  {
    return $this->email;
  }

  public function setLockToDomain(string $lockToDomain): void
  {
    $this->lockToDomain = $lockToDomain;
  }

  public function getLockToDomain(): string
  {
    return $this->lockToDomain;
  }

  public function setTitle(string $title): void
  {
    $this->title = $title;
  }

  public function getTitle(): string
  {
    return $this->title;
  }

  public function setZip(string $zip): void
  {
    $this->zip = $zip;
  }

  public function getZip(): string
  {
    return $this->zip;
  }

  public function setCity(string $city): void
  {
    $this->city = $city;
  }

  public function getCity(): string
  {
    return $this->city;
  }

  public function setCountry(string $country): void
  {
    $this->country = $country;
  }

  public function getCountry(): string
  {
    return $this->country;
  }

  public function setWww(string $www): void
  {
    $this->www = $www;
  }

  public function getWww(): string
  {
    return $this->www;
  }

  public function setCompany(string $company): void
  {
    $this->company = $company;
  }

  public function getCompany(): string
  {
    return $this->company;
  }

  /**
   * Sets the image value.
   * @param ObjectStorage<FileReference> $image
   */
  public function setImage(ObjectStorage $image): void
  {
    $this->image = $image;
  }

  /**
   * Gets the image value.
   *
   * @return ObjectStorage<FileReference>
   */
  public function getImage(): ObjectStorage
  {
    return $this->image;
  }

  /**
   * Sets the lastlogin value.
   */
  public function setLastlogin(DateTime $lastlogin): void
  {
    $this->lastlogin = $lastlogin;
  }

  /**
   * Returns the lastlogin value.
   *
   * @return DateTime
   */
  public function getLastlogin(): DateTime|null
  {
    return $this->lastlogin;
  }

  public function setLastreminderemailsent(int $timestamp): void
  {
    $this->lastreminderemailsent = $timestamp;
  }

  public function getLastreminderemailsent(): int
  {
    return $this->lastreminderemailsent;
  }

  public function setHidden(bool $hidden): void
  {
    $this->hidden = $hidden;
  }

  public function getHidden(): bool
  {
    return $this->hidden;
  }
}
