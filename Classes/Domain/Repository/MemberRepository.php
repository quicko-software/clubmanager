<?php

namespace Quicko\Clubmanager\Domain\Repository;

use DateTime;
use Quicko\Clubmanager\Domain\Model\Member;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * @template T of Member
 *
 * @extends Repository<T>
 */
class MemberRepository extends Repository
{
  use PersistAndRefetchTrait;

  /**
   * Summary of findByFeUserName.
   *
   * @return T|null
   */
  public function findByFeUserName(string $feUserName): ?object
  {
    $query = $this->createQuery();
    $this->disableQueryRestrictions($query);
    $query->matching(
      $query->logicalAnd(
        $query->equals('feuser.username', $feUserName),
        $query->equals('state', Member::STATE_ACTIVE),
      )
    );

    $member = $query->execute()->getFirst();

    return $member;
  }

  /**
   * Summary of findByFeUserNameWithHiddenLocations.
   *
   * @return T|null
   */
  public function findByFeUserNameWithHiddenLocations(LocationRepository $locationRepository, string $feUserName): ?object
  {
    $member = $this->findByFeUserName($feUserName);
    if ($member && $memberUid = $member->getUid()) {
      $loc = $locationRepository->findMainLocByMemberUidWithHidden($memberUid);
      if ($loc) {
        $member->setMainLocation($loc);
      }
      $subLocs = $locationRepository->findSubLocsByMemberUidWithHidden($memberUid);

      foreach ($subLocs as $subLoc) {
        $member->getSubLocations()->attach($subLoc);
      }
    }

    return $member;
  }

  /**
   * Summary of disableQueryRestrictions.
   *
   * @param QueryInterface<T> $query
   */
  protected function disableQueryRestrictions(QueryInterface $query): void
  {
    $querySettings = $query->getQuerySettings();
    $querySettings
      ->setIgnoreEnableFields(true)
      ->setRespectStoragePage(false);
  }

  /**
   * @return iterable<int, T>
   */
  public function findAllEndedWithWrongState(DateTime $refDate): iterable
  {
    $query = $this->createQuery();
    $query->getQuerySettings()->setRespectStoragePage(false);
    $query->getQuerySettings()->setIgnoreEnableFields(true);
    $query->matching(
      $query->logicalAnd(
        $query->equals('state', Member::STATE_ACTIVE),
        $query->lessThanOrEqual('endtime', $refDate),
        $query->greaterThan('endtime', 0),
        $query->logicalNot(
          $query->equals('endtime', null)
        )
      ),
    );

    return $query->execute();
  }

  /**
   * @return iterable<int, T>
   */
  public function findAllCanceleddWithWrongState(): iterable
  {
    $query = $this->createQuery();
    $query->getQuerySettings()->setRespectStoragePage(false);
    $query->getQuerySettings()->setIgnoreEnableFields(true);
    $query->matching(
      $query->logicalAnd(
        $query->equals('state', Member::STATE_CANCELLED),
        $query->logicalOr(
          $query->equals('endtime', null),
          $query->equals('endtime', 0)
        )
      ),
    );

    return $query->execute();
  }

  /**
   * Summary of findAllActiveInPid.
   *
   * @return iterable<int, T>
   */
  public function findAllActiveInPid(int $pid, DateTime $endDate): iterable
  {
    $query = $this->createQuery();
    $query->getQuerySettings()->setRespectStoragePage(false);
    $query->getQuerySettings()->setEnableFieldsToBeIgnored(['endtime', 'starttime']);
    $query->matching(
      $query->logicalAnd(
        $query->equals('pid', $pid),
        $query->logicalOr(
          $query->equals('endtime', null),
          $query->equals('endtime', 0),
          $query->lessThanOrEqual('endtime', $endDate)
        ),
        $query->equals('state', Member::STATE_ACTIVE),
      )
    );

    return $query->execute();
  }

  /**
   * Summary of findOneByEmailAndPid.
   *
   * @return iterable<int, T>
   */
  public function findOneByEmailAndPid(string $email, int $pid): iterable
  {
    $query = $this->createQuery();
    $querySettings = $query->getQuerySettings();
    $querySettings->setRespectStoragePage(false);
    // but not: ->setIgnoreEnabledFields(true) because we want all active, visible etc. user
    $constraints = [
      $query->equals('email', $email),
      $query->equals('pid', $pid),
    ];

    $result = $query->matching(
      $query->logicalAnd(...$constraints)
    )
      ->execute();

    return $result;
  }

  /**
   * @param int[] $memberPidList list of pids where to look for members, e.g. [12,488,7] or null
   *
   * @return iterable<int, T>
   */
  public function findMemberRoRemind(int $minDaysSinceLastEmail, array $memberPidList): iterable
  {
    $query = $this->createQuery();
    $querySettings = $query->getQuerySettings();
    $querySettings->setRespectStoragePage(false);
    // but not: ->setIgnoreEnabledFields(true) because we want all active, visible etc. user

    $minElapsedTimeSec = $minDaysSinceLastEmail * 24 * 60 * 60;
    $xDaysAgoSec = time() - $minElapsedTimeSec;

    $constraints = [
      $query->equals('state', Member::STATE_ACTIVE), // only active member
      $query->equals('feuser.lastlogin', 0), // that never logged in
      $query->lessThan('feuser.lastreminderemailsent', $xDaysAgoSec), // and who got their last reminder at least X days ago
    ];
    if (count($memberPidList) > 0) {
      $constraints[] = $query->in('pid', $memberPidList);
    }

    $result = $query->matching(
      $query->logicalAnd(...$constraints)
    )
      ->execute();

    return $result;
  }

  /**
   * Summary of findActivePublic.
   *
   * @param string[] $sorting
   *
   * @return iterable<int, T>
   */
  public function findActivePublic(?array $sorting = null): iterable
  {
    $query = $this->createQuery();
    $query->matching(
      $query->equals('state', Member::STATE_ACTIVE),
    );
    if ($sorting != null) {
      $query->setOrderings($sorting);
    }

    return $query->execute();
  }

  /**
   * Summary of findByUidWithoutStoragePage.
   *
   * @return T|null
   */
  public function findByUidWithoutStoragePage(int $uid): ?object
  {
    $query = $this->createQuery();
    $querySettings = $query->getQuerySettings();
    $querySettings->setRespectStoragePage(false);
    $constraints = [
      $query->equals('uid', $uid),
    ];

    $result = $query->matching(
      $query->logicalAnd(...$constraints)
    )
      ->execute()->getFirst();

    return $result;
  }

  /**
   * @param int[] $uids
   *
   * @return iterable<int, T>
   */
  public function findAllByUids(array $uids): iterable
  {
    $query = $this->createQuery();
    $querySettings = $query->getQuerySettings();
    $querySettings->setRespectStoragePage(false);
    $querySettings->setIgnoreEnableFields(true);
    $constraints = [
      $query->in('uid', $uids),
    ];

    $result = $query->matching(
      $query->logicalAnd(...$constraints)
    )
      ->execute();

    return $result;
  }
}
