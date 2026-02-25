<?php

namespace Quicko\Clubmanager\Service;

use Quicko\Clubmanager\Domain\Model\Member;
use Quicko\Clubmanager\Domain\Model\MemberJournalEntry;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class MemberJournalProjectionService
{
  public function projectMemberConsistency(int $memberUid): bool
  {
    if ($memberUid <= 0) {
      return false;
    }

    $memberRecord = $this->getMemberRecord($memberUid);
    if (!is_array($memberRecord)) {
      return false;
    }

    $updates = [];
    $lastStatusEntry = $this->findLastProcessedStatusEntryForProjection($memberUid);
    $pendingCancellation = $this->findPendingCancellationStatusChange($memberUid);

    if (is_array($lastStatusEntry)) {
      $expectedState = (int) ($lastStatusEntry['target_state'] ?? Member::STATE_UNSET);
      if ((int) ($memberRecord['state'] ?? Member::STATE_UNSET) !== $expectedState) {
        $updates['state'] = $expectedState;
      }

      $effectiveDate = (int) ($lastStatusEntry['effective_date'] ?? 0);
      if ($expectedState === Member::STATE_ACTIVE && (int) ($memberRecord['starttime'] ?? 0) <= 0 && $effectiveDate > 0) {
        $updates['starttime'] = $effectiveDate;
      }

      if ($expectedState === Member::STATE_CANCELLED && $effectiveDate > 0) {
        if ((int) ($memberRecord['endtime'] ?? 0) !== $effectiveDate) {
          $updates['endtime'] = $effectiveDate;
        }
      } elseif ($pendingCancellation === null && (int) ($memberRecord['endtime'] ?? 0) > 0) {
        $updates['endtime'] = 0;
      }
    } elseif ($pendingCancellation === null && (int) ($memberRecord['endtime'] ?? 0) > 0) {
      // Ohne wirksamen Statuswechsel darf kein stale endtime verbleiben.
      $updates['endtime'] = 0;
    }

    $lastLevelEntry = $this->findLastProcessedLevelChangeEntry($memberUid);
    if (is_array($lastLevelEntry)) {
      $expectedLevel = (int) ($lastLevelEntry['new_level'] ?? 0);
      if ((int) ($memberRecord['level'] ?? 0) !== $expectedLevel) {
        $updates['level'] = $expectedLevel;
      }
    }

    if ($updates === []) {
      return false;
    }

    $updates['tstamp'] = time();

    $connection = GeneralUtility::makeInstance(ConnectionPool::class)
      ->getConnectionForTable('tx_clubmanager_domain_model_member');
    $connection->update(
      'tx_clubmanager_domain_model_member',
      $updates,
      [
        'uid' => $memberUid,
        'deleted' => 0,
      ]
    );

    return true;
  }

  public function applyPendingFutureCancellationEndtime(int $memberUid): bool
  {
    if ($memberUid <= 0) {
      return false;
    }

    $pendingCancellation = $this->findPendingCancellationStatusChange($memberUid);
    if (!is_array($pendingCancellation)) {
      return false;
    }

    $effectiveDate = (int) ($pendingCancellation['effective_date'] ?? 0);
    if ($effectiveDate <= time()) {
      return false;
    }

    $memberRecord = $this->getMemberRecord($memberUid);
    if (!is_array($memberRecord)) {
      return false;
    }

    if ((int) ($memberRecord['endtime'] ?? 0) === $effectiveDate) {
      return false;
    }

    $connection = GeneralUtility::makeInstance(ConnectionPool::class)
      ->getConnectionForTable('tx_clubmanager_domain_model_member');
    $connection->update(
      'tx_clubmanager_domain_model_member',
      [
        'endtime' => $effectiveDate,
        'tstamp' => time(),
      ],
      [
        'uid' => $memberUid,
        'deleted' => 0,
      ]
    );

    return true;
  }

  private function getMemberRecord(int $memberUid): ?array
  {
    $queryBuilder = $this->createQueryBuilder('tx_clubmanager_domain_model_member');
    $record = $queryBuilder
      ->select('uid', 'state', 'starttime', 'endtime', 'level')
      ->from('tx_clubmanager_domain_model_member')
      ->where(
        $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($memberUid)),
        $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0))
      )
      ->setMaxResults(1)
      ->executeQuery()
      ->fetchAssociative();

    return is_array($record) ? $record : null;
  }

  private function findLastProcessedStatusEntryForProjection(int $memberUid): ?array
  {
    $entry = $this->findLastProcessedStatusEntry($memberUid, true);
    if (is_array($entry)) {
      return $entry;
    }

    return $this->findLastProcessedStatusEntry($memberUid, false);
  }

  private function findLastProcessedStatusEntry(int $memberUid, bool $excludeApplied): ?array
  {
    $queryBuilder = $this->createQueryBuilder('tx_clubmanager_domain_model_memberjournalentry');
    $constraints = [
      $queryBuilder->expr()->eq('member', $queryBuilder->createNamedParameter($memberUid)),
      $queryBuilder->expr()->eq('entry_type', $queryBuilder->createNamedParameter(MemberJournalEntry::ENTRY_TYPE_STATUS_CHANGE)),
      $queryBuilder->expr()->isNotNull('processed'),
      $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0)),
      $queryBuilder->expr()->eq('hidden', $queryBuilder->createNamedParameter(0)),
    ];

    if ($excludeApplied) {
      $constraints[] = $queryBuilder->expr()->neq('target_state', $queryBuilder->createNamedParameter(Member::STATE_APPLIED));
    }

    $entry = $queryBuilder
      ->select('uid', 'target_state', 'effective_date', 'entry_date')
      ->from('tx_clubmanager_domain_model_memberjournalentry')
      ->where(...$constraints)
      ->orderBy('effective_date', 'DESC')
      ->addOrderBy('entry_date', 'DESC')
      ->setMaxResults(1)
      ->executeQuery()
      ->fetchAssociative();

    return is_array($entry) ? $entry : null;
  }

  private function findLastProcessedLevelChangeEntry(int $memberUid): ?array
  {
    $queryBuilder = $this->createQueryBuilder('tx_clubmanager_domain_model_memberjournalentry');
    $entry = $queryBuilder
      ->select('uid', 'new_level')
      ->from('tx_clubmanager_domain_model_memberjournalentry')
      ->where(
        $queryBuilder->expr()->eq('member', $queryBuilder->createNamedParameter($memberUid)),
        $queryBuilder->expr()->eq('entry_type', $queryBuilder->createNamedParameter(MemberJournalEntry::ENTRY_TYPE_LEVEL_CHANGE)),
        $queryBuilder->expr()->isNotNull('processed'),
        $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0)),
        $queryBuilder->expr()->eq('hidden', $queryBuilder->createNamedParameter(0))
      )
      ->orderBy('effective_date', 'DESC')
      ->addOrderBy('entry_date', 'DESC')
      ->setMaxResults(1)
      ->executeQuery()
      ->fetchAssociative();

    return is_array($entry) ? $entry : null;
  }

  private function findPendingCancellationStatusChange(int $memberUid): ?array
  {
    $queryBuilder = $this->createQueryBuilder('tx_clubmanager_domain_model_memberjournalentry');
    $entry = $queryBuilder
      ->select('uid', 'effective_date')
      ->from('tx_clubmanager_domain_model_memberjournalentry')
      ->where(
        $queryBuilder->expr()->eq('member', $queryBuilder->createNamedParameter($memberUid)),
        $queryBuilder->expr()->eq('entry_type', $queryBuilder->createNamedParameter(MemberJournalEntry::ENTRY_TYPE_STATUS_CHANGE)),
        $queryBuilder->expr()->eq('target_state', $queryBuilder->createNamedParameter(Member::STATE_CANCELLED)),
        $queryBuilder->expr()->isNull('processed'),
        $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0)),
        $queryBuilder->expr()->eq('hidden', $queryBuilder->createNamedParameter(0))
      )
      ->orderBy('entry_date', 'DESC')
      ->setMaxResults(1)
      ->executeQuery()
      ->fetchAssociative();

    return is_array($entry) ? $entry : null;
  }

  private function createQueryBuilder(string $table): QueryBuilder
  {
    $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
    $queryBuilder->getRestrictions()->removeAll();
    return $queryBuilder;
  }
}

