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
    $query->matching(
      $query->logicalAnd(
        $query->in('entryType', [
          MemberJournalEntry::ENTRY_TYPE_STATUS_CHANGE,
          MemberJournalEntry::ENTRY_TYPE_LEVEL_CHANGE
        ]),
        $query->equals('processed', null),
        $query->lessThanOrEqual('effectiveDate', $date),
        $query->greaterThan('effectiveDate', 0)
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
        $query->equals('entryType', MemberJournalEntry::ENTRY_TYPE_CANCELLATION_REQUEST)
      )
    );
    $query->setOrderings(['entryDate' => QueryInterface::ORDER_DESCENDING]);

    return $query->execute()->getFirst();
  }
}


