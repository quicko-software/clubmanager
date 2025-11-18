<?php

namespace Quicko\Clubmanager\Domain\Repository;

use DateTime;
use Quicko\Clubmanager\Domain\Model\MemberJournalEntry;
use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * @template T of MemberJournalEntry
 *
 * @extends Repository<T>
 */
class MemberJournalEntryRepository extends Repository
{
  /**
   * Findet alle unverarbeiteten Einträge bis zum Datum
   *
   * @return iterable<int, T>
   */
  public function findPendingUntilDate(DateTime $date): iterable
  {
    $query = $this->createQuery();
    $query->getQuerySettings()->setRespectStoragePage(false);

    // Konvertiere DateTime zu Timestamp für den Vergleich
    $timestamp = $date->getTimestamp();

    $query->matching(
      $query->logicalAnd(
        $query->in('entryType', [
          MemberJournalEntry::ENTRY_TYPE_STATUS_CHANGE,
          MemberJournalEntry::ENTRY_TYPE_LEVEL_CHANGE
        ]),
        $query->equals('processed', null),
        $query->lessThanOrEqual('effectiveDate', $timestamp),
        $query->greaterThan('effectiveDate', 0),
        $query->equals('deleted', 0),
        $query->equals('hidden', 0)
      )
    );
    $query->setOrderings(['effectiveDate' => QueryInterface::ORDER_ASCENDING]);

    return $query->execute();
  }

  /**
   * Findet alle unverarbeiteten Einträge bis zum Datum für einen spezifischen Member
   *
   * @return iterable<int, T>
   */
  public function findPendingUntilDateForMember(DateTime $date, int $memberUid): iterable
  {
    $query = $this->createQuery();
    $querySettings = $query->getQuerySettings();
    $querySettings->setRespectStoragePage(false);
    $querySettings->setIgnoreEnableFields(true); // Ignore enable fields, wir prüfen explizit
    $query->setQuerySettings($querySettings);

    // Konvertiere DateTime zu Timestamp für den Vergleich
    $timestamp = $date->getTimestamp();

    $query->matching(
      $query->logicalAnd(
        $query->equals('member', $memberUid),
        $query->in('entryType', [
          MemberJournalEntry::ENTRY_TYPE_STATUS_CHANGE,
          MemberJournalEntry::ENTRY_TYPE_LEVEL_CHANGE
        ]),
        $query->equals('processed', null),
        $query->lessThanOrEqual('effectiveDate', $timestamp),
        $query->greaterThan('effectiveDate', 0),
        $query->equals('deleted', 0),
        $query->equals('hidden', 0)
      )
    );
    $query->setOrderings(['effectiveDate' => QueryInterface::ORDER_ASCENDING]);

    return $query->execute();
  }

  /**
   * Vollständiges Journal für ein Member
   *
   * @return iterable<int, T>
   */
  public function findAllForMember(int $memberUid): iterable
  {
    $query = $this->createQuery();
    $query->getQuerySettings()->setRespectStoragePage(false);
    $query->matching($query->equals('member', $memberUid));
    $query->setOrderings(['entryDate' => QueryInterface::ORDER_DESCENDING]);

    return $query->execute();
  }

  /**
   * Prüft ob ein Member einen offenen Kündigungswunsch hat
   *
   * @return T|null
   */
  public function findPendingCancellationRequest(int $memberUid): ?object
  {
    $query = $this->createQuery();
    $query->getQuerySettings()->setRespectStoragePage(false);
    $query->matching(
      $query->logicalAnd(
        $query->equals('member', $memberUid),
        $query->equals('entryType', MemberJournalEntry::ENTRY_TYPE_CANCELLATION_REQUEST),
        $query->equals('deleted', 0),
        $query->equals('hidden', 0)
      )
    );
    $query->setOrderings(['entryDate' => QueryInterface::ORDER_DESCENDING]);

    return $query->execute()->getFirst();
  }

  /**
   * Findet den zugehörigen Status-Wechsel-Eintrag für einen Kündigungswunsch
   *
   * @return T|null
   */
  public function findPendingCancellationStatusChange(int $memberUid): ?object
  {
    $query = $this->createQuery();
    $query->getQuerySettings()->setRespectStoragePage(false);
    $query->matching(
      $query->logicalAnd(
        $query->equals('member', $memberUid),
        $query->equals('entryType', MemberJournalEntry::ENTRY_TYPE_STATUS_CHANGE),
        $query->equals('targetState', \Quicko\Clubmanager\Domain\Model\Member::STATE_CANCELLED),
        $query->equals('processed', null),
        $query->equals('deleted', 0),
        $query->equals('hidden', 0)
      )
    );
    $query->setOrderings(['entryDate' => QueryInterface::ORDER_DESCENDING]);

    return $query->execute()->getFirst();
  }

  /**
   * Findet den letzten verarbeiteten Eintrag eines bestimmten Typs
   *
   * @return T|null
   */
  public function findLastProcessedEntry(int $memberUid, string $entryType): ?object
  {
    $query = $this->createQuery();
    $query->getQuerySettings()->setRespectStoragePage(false);
    $query->matching(
      $query->logicalAnd(
        $query->equals('member', $memberUid),
        $query->equals('entryType', $entryType),
        $query->logicalNot($query->equals('processed', null)),
        $query->equals('deleted', 0),
        $query->equals('hidden', 0)
      )
    );
    $query->setOrderings(['entryDate' => QueryInterface::ORDER_DESCENDING]);

    return $query->execute()->getFirst();
  }
}


