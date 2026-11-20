<?php

declare(strict_types=1);

namespace Quicko\Clubmanager\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Mitgliedschaftstyp (z. B. Person, Einrichtung) als pflegbarer Datensatz.
 *
 * Ein deaktivierter Typ bleibt an bestehenden Mitgliedern lesbar, wird aber für
 * neue Zuordnungen nicht mehr angeboten.
 */
class MembershipType extends AbstractEntity
{
  protected string $title = '';

  protected int $personType = Member::PERSON_TYPE_NATURAL;

  protected bool $inactive = false;

  public function getTitle(): string
  {
    return $this->title;
  }

  public function setTitle(string $title): void
  {
    $this->title = $title;
  }

  public function getPersonType(): int
  {
    return $this->personType;
  }

  public function setPersonType(int $personType): void
  {
    $this->personType = $personType;
  }

  public function isLegalPerson(): bool
  {
    return $this->personType === Member::PERSON_TYPE_JURIDICAL;
  }

  public function isInactive(): bool
  {
    return $this->inactive;
  }

  public function setInactive(bool $inactive): void
  {
    $this->inactive = $inactive;
  }
}
