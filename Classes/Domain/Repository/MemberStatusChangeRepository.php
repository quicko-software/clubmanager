<?php

namespace Quicko\Clubmanager\Domain\Repository;

use DateTime;
use Quicko\Clubmanager\Domain\Model\MemberStatusChange;
use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * @extends Repository<MemberStatusChange>
 */
class MemberStatusChangeRepository extends Repository
{
  /**
   * Findet alle fälligen, noch nicht verarbeiteten StatusChanges
   *
   * @return iterable<int, MemberStatusChange>
   */
  public function findPendingUntilDate(DateTime $date): iterable
  {
    $query = $this->createQuery();
    $query->getQuerySettings()->setRespectStoragePage(false);
    $query->matching(
      $query->logicalAnd(
        $query->equals('processed', false),
        $query->lessThanOrEqual('effectiveDate', $date),
        $query->greaterThan('effectiveDate', 0)
      )
    );
    $query->setOrderings(['effectiveDate' => QueryInterface::ORDER_ASCENDING]);

    return $query->execute();
  }

  /**
   * Findet den aktuell gültigen StatusChange für ein Member
   *
   * @return MemberStatusChange|null
   */
  public function findCurrentForMember(int $memberUid, DateTime $refDate): ?MemberStatusChange
  {
    $query = $this->createQuery();
    $query->getQuerySettings()->setRespectStoragePage(false);
    $query->matching(
      $query->logicalAnd(
        $query->equals('member', $memberUid),
        $query->lessThanOrEqual('effectiveDate', $refDate),
        $query->greaterThan('effectiveDate', 0)
      )
    );
    $query->setOrderings(['effectiveDate' => QueryInterface::ORDER_DESCENDING]);
    $query->setLimit(1);

    $result = $query->execute()->getFirst();
    return $result instanceof MemberStatusChange ? $result : null;
  }
}

