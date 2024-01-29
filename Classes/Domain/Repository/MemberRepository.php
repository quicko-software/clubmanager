<?php

namespace Quicko\Clubmanager\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

use Quicko\Clubmanager\Domain\Model\Member;
use Quicko\Clubmanager\Domain\Repository\LocationRepository;
use Quicko\Clubmanager\Domain\Repository\PersistAndRefetchTrait;


class MemberRepository extends Repository
{
  use PersistAndRefetchTrait;

  public function findByFeUserName($feUserName) : ?Member
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

  public function findByFeUserNameWithHiddenLocations(LocationRepository $locationRepository, string $feUserName) : ?Member
  {
    /** @var Member $member */
    $member = $this->findByFeUserName($feUserName);
    if ($member) {
      $loc = $locationRepository->findMainLocByMemberUidWithHidden($member->getUid());
      if ($loc) {
        $member->setMainLocation($loc);
      }
      $subLocs = $locationRepository->findSubLocsByMemberUidWithHidden($member->getUid());
      $member->setSubLocations($subLocs->toArray());
    }
    return $member;
  }

  protected function disableQueryRestrictions(QueryInterface $query) : void
  {
    $querySettings = $query->getQuerySettings();
    $querySettings
      ->setIgnoreEnableFields(true)
      ->setRespectStoragePage(false);
  }

  /**
   * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface|object[] 
   */
  public function findAllEndedWithWrongState(\DateTime $refDate)
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
   * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface|object[] 
   */
  public function findAllCanceleddWithWrongState()
  {
    $query = $this->createQuery();
    $query->getQuerySettings()->setRespectStoragePage(false);
    $query->getQuerySettings()->setIgnoreEnableFields(true);
    $query->matching(
      $query->logicalAnd(
        $query->equals('state', Member::STATE_CANCELLED),
        $query->logicalOr(
          $query->equals('endtime', NULL),
          $query->equals('endtime', 0)
        )
      ),
    );
    return $query->execute();
  }

  public function findAllActiveInPid($pid, $endDate)
  {
    $query = $this->createQuery();
    $query->getQuerySettings()->setRespectStoragePage(false);
    $query->getQuerySettings()->setEnableFieldsToBeIgnored(array('endtime', 'starttime'));
    $query->matching(
      $query->logicalAnd([
        $query->equals('pid', $pid),
        $query->logicalOr(
          $query->equals('endtime', NULL),
          $query->equals('endtime', 0),
          $query->lessThanOrEqual('endtime', $endDate)
        ),
        $query->equals('state', Member::STATE_ACTIVE),
      ])
    );

    return $query->execute();
  }

  public function findOneByEmailAndPid(string $email, int $pid)
  {
    $query = $this->createQuery();
    $querySettings = $query->getQuerySettings();
    $querySettings->setRespectStoragePage(false);
    // but not: ->setIgnoreEnabledFields(true) because we want all active, visible etc. user
    $constraints = [
      $query->equals('email', $email),
      $query->equals('pid', $pid)
    ];

    $result = $query->matching(
      $query->logicalAnd($constraints)
    )
      ->execute();
    return $result;
  }

  /**
   * @param int $minDaysSinceLastEmail
   * @param array $memberPidList list of pids where to look for members, e.g. [12,488,7] or null
   * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface|Member[]
   */
  public function findMemberRoRemind(int $minDaysSinceLastEmail, array $memberPidList)
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
    if (is_array($memberPidList) && count($memberPidList) > 0) {
      $constraints[] = $query->in('pid', $memberPidList);
    }

    $result = $query->matching(
      $query->logicalAnd(...$constraints)
    )
      ->execute();
    return $result;
  }


  public function findActivePublic(?array $sorting = null)
  {
    $query = $this->createQuery();
    $query->matching(
      $query->equals('state', \Quicko\Clubmanager\Domain\Model\Member::STATE_ACTIVE),
    );
    if ($sorting != null) {
      $query->setOrderings($sorting);
    }

    return $query->execute();
  }

  public function findByUidWithoutStoragePage(int $uid)
  {
    $query = $this->createQuery();
    $querySettings = $query->getQuerySettings();
    $querySettings->setRespectStoragePage(false);
    $constraints = [
      $query->equals('uid', $uid),
    ];

    $result = $query->matching(
      $query->logicalAnd($constraints)
    )
      ->execute()->getFirst();
    return $result;
  }
}
