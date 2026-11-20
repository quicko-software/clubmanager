<?php

declare(strict_types=1);

namespace Quicko\Clubmanager\Domain\Model;

use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Mitglieds-Level als pflegbarer Datensatz.
 *
 * `ranking` ordnet die Level (höher = höherwertig) und ersetzt Vergleiche auf die
 * früheren festen Stufen 0/10/20/30. `displayClass` ersetzt die Farbklassen im Frontend.
 */
class MemberLevel extends AbstractEntity
{
  protected string $title = '';

  protected int $ranking = 0;

  protected string $displayClass = '';

  protected bool $inactive = false;

  /**
   * @var ObjectStorage<MembershipType>
   */
  #[Lazy]
  protected ObjectStorage $membershipTypes;

  public function __construct()
  {
    $this->initializeObject();
  }

  public function initializeObject(): void
  {
    $this->membershipTypes = new ObjectStorage();
  }

  public function getTitle(): string
  {
    return $this->title;
  }

  public function setTitle(string $title): void
  {
    $this->title = $title;
  }

  public function getRanking(): int
  {
    return $this->ranking;
  }

  public function setRanking(int $ranking): void
  {
    $this->ranking = $ranking;
  }

  public function getDisplayClass(): string
  {
    return $this->displayClass;
  }

  public function setDisplayClass(string $displayClass): void
  {
    $this->displayClass = $displayClass;
  }

  public function isInactive(): bool
  {
    return $this->inactive;
  }

  public function setInactive(bool $inactive): void
  {
    $this->inactive = $inactive;
  }

  /**
   * @return ObjectStorage<MembershipType>
   */
  public function getMembershipTypes(): ObjectStorage
  {
    return $this->membershipTypes;
  }

  /**
   * @param ObjectStorage<MembershipType> $membershipTypes
   */
  public function setMembershipTypes(ObjectStorage $membershipTypes): void
  {
    $this->membershipTypes = $membershipTypes;
  }

  public function addMembershipType(MembershipType $membershipType): void
  {
    $this->membershipTypes->attach($membershipType);
  }

  public function removeMembershipType(MembershipType $membershipType): void
  {
    $this->membershipTypes->detach($membershipType);
  }
}
