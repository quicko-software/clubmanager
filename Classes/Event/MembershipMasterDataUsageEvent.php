<?php

declare(strict_types=1);

namespace Quicko\Clubmanager\Event;

use Quicko\Clubmanager\Service\MembershipMasterDataService;

/**
 * Fragt ab, wo ein Mitgliedschaftstyp oder Level verwendet wird (#6425).
 *
 * Der Kern zählt Mitglieder und Journal-Einträge selbst. Extensions, die Typen oder Level
 * referenzieren (z. B. Beitragssätze in Billing), melden ihre Verwendungen über
 * `addUsage()`. Ist mindestens eine Verwendung gemeldet, weist der Kern das Löschen ab;
 * der Datensatz kann stattdessen deaktiviert werden.
 */
final class MembershipMasterDataUsageEvent
{
  /** @var array<string, int> */
  private array $usages = [];

  /**
   * @param string $table `MembershipMasterDataService::TABLE_TYPE` oder `::TABLE_LEVEL`
   */
  public function __construct(
    private readonly string $table,
    private readonly int $uid,
  ) {}

  public function getTable(): string
  {
    return $this->table;
  }

  public function getUid(): int
  {
    return $this->uid;
  }

  public function isMembershipType(): bool
  {
    return $this->table === MembershipMasterDataService::TABLE_TYPE;
  }

  public function isMemberLevel(): bool
  {
    return $this->table === MembershipMasterDataService::TABLE_LEVEL;
  }

  /**
   * @param string $label lesbare, bereits übersetzte Bezeichnung, z. B. „Beitragssätze“
   * @param int $count Anzahl der Verwendungen; 0 und negative Werte werden ignoriert
   */
  public function addUsage(string $label, int $count): void
  {
    if ($count <= 0 || $label === '') {
      return;
    }
    $this->usages[$label] = ($this->usages[$label] ?? 0) + $count;
  }

  /**
   * @return array<string, int> Bezeichnung => Anzahl
   */
  public function getUsages(): array
  {
    return $this->usages;
  }

  public function isUsed(): bool
  {
    return $this->usages !== [];
  }
}
