<?php

namespace Quicko\Clubmanager\Hooks;

use Quicko\Clubmanager\Domain\Model\Member;
use Quicko\Clubmanager\Utils\HookUtils;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Hook to automatically create a FE user when a member becomes active.
 *
 * IMPORTANT: This hook must be registered AFTER ProcessMemberJournalHook,
 * because the journal processing may change the member status.
 */
class MemberAutoCreateFeuserHook
{
  protected Logger $logger;

  public function __construct(?Logger $logger = null)
  {
    if ($logger === null) {
      /** @var LogManager $logManager */
      $logManager = GeneralUtility::makeInstance(LogManager::class);
      $this->logger = $logManager->getLogger(__CLASS__);
    } else {
      $this->logger = $logger;
    }
  }

  private function getDataHandler(): DataHandler
  {
    return GeneralUtility::makeInstance(DataHandler::class);
  }

  private function getQueryBuilder(string $tableName): QueryBuilder
  {
    $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($tableName);
    $queryBuilder->getRestrictions()->removeAll();
    return $queryBuilder;
  }

  private function hasExistingFeuser(int $memberUid): bool
  {
    $queryBuilder = $this->getQueryBuilder('fe_users');
    $record = $queryBuilder
      ->select('uid')
      ->from('fe_users')
      ->where(
        $queryBuilder->expr()->eq('clubmanager_member', $queryBuilder->createNamedParameter($memberUid, Connection::PARAM_INT)),
        $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, Connection::PARAM_INT))
      )
      ->setMaxResults(1)
      ->executeQuery()
      ->fetchAssociative();

    return is_array($record);
  }

  private function isUsernameTaken(string $username): bool
  {
    $queryBuilder = $this->getQueryBuilder('fe_users');
    $record = $queryBuilder
      ->select('uid')
      ->from('fe_users')
      ->where(
        $queryBuilder->expr()->eq('username', $queryBuilder->createNamedParameter($username)),
        $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, Connection::PARAM_INT))
      )
      ->setMaxResults(1)
      ->executeQuery()
      ->fetchAssociative();

    return is_array($record);
  }

  private function resolveUsername(array $memberRecord): string
  {
    $memberUid = (int) ($memberRecord['uid'] ?? 0);
    $ident = trim((string) ($memberRecord['ident'] ?? ''));
    $email = trim((string) ($memberRecord['email'] ?? ''));

    $candidate = $ident ?: ($email ?: ('member-' . $memberUid));
    if ($candidate === '') {
      $candidate = 'member-' . $memberUid;
    }

    if (!$this->isUsernameTaken($candidate)) {
      return $candidate;
    }

    $fallback = $candidate . '-' . $memberUid;
    if (!$this->isUsernameTaken($fallback)) {
      return $fallback;
    }

    $suffix = 1;
    do {
      $fallback = $candidate . '-' . $memberUid . '-' . $suffix;
      $suffix++;
    } while ($this->isUsernameTaken($fallback));

    return $fallback;
  }

  /**
   * @param array<string, mixed> $memberRecord
   */
  private function createFeuser(array $memberRecord): void
  {
    $memberUid = (int) ($memberRecord['uid'] ?? 0);
    if ($memberUid <= 0) {
      return;
    }

    /** @var ExtensionConfiguration $extConf */
    $extConf = GeneralUtility::makeInstance(ExtensionConfiguration::class);
    $storagePid = (int) $extConf->get('clubmanager', 'feUsersStoragePid');
    if ($storagePid <= 0) {
      $this->logger->error('Cannot create fe_user: feUsersStoragePid is not configured.');
      return;
    }

    $userGroup = trim((string) $extConf->get('clubmanager', 'defaultFeUserGroupUid'));
    $username = $this->resolveUsername($memberRecord);

    $data = [];
    $newId = 'NEW' . uniqid();
    $data['fe_users'][$newId] = [
      'pid' => $storagePid,
      'username' => $username,
      'email' => (string) ($memberRecord['email'] ?? ''),
      'clubmanager_member' => $memberUid,
      'password' => '',
    ];

    if ($userGroup !== '') {
      $data['fe_users'][$newId]['usergroup'] = $userGroup;
    }

    $dataHandler = $this->getDataHandler();
    $dataHandler->start($data, []);
    $commandResult = $dataHandler->process_datamap();
    if ($commandResult === false) {
      HookUtils::logError($this->logger, 'fe_users', $newId);
    }
  }

  /**
   * Fetch member record directly from DB to avoid any caching issues.
   *
   * @return array<string, mixed>|null
   */
  private function getMemberRecordFromDb(int $memberUid): ?array
  {
    $queryBuilder = $this->getQueryBuilder('tx_clubmanager_domain_model_member');
    $record = $queryBuilder
      ->select('uid', 'state', 'ident', 'email')
      ->from('tx_clubmanager_domain_model_member')
      ->where(
        $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($memberUid, Connection::PARAM_INT)),
        $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, Connection::PARAM_INT))
      )
      ->setMaxResults(1)
      ->executeQuery()
      ->fetchAssociative();

    return is_array($record) ? $record : null;
  }

  /**
   * Check a single member and create FE user if needed.
   */
  private function processActiveMember(int $memberUid): void
  {
    if ($memberUid <= 0) {
      return;
    }

    // Use direct DB query to avoid caching issues
    $record = $this->getMemberRecordFromDb($memberUid);
    if ($record === null) {
      $this->logger->debug('processActiveMember: Member record not found', ['memberUid' => $memberUid]);
      return;
    }

    $state = (int) $record['state'];
    if ($state !== Member::STATE_ACTIVE) {
      $this->logger->debug('processActiveMember: Member not active, skipping', [
        'memberUid' => $memberUid,
        'state' => $state,
      ]);
      return;
    }

    // Prüfe ob ident vorhanden ist - ohne ident kann kein sinnvoller FE-User erstellt werden
    $ident = trim((string) ($record['ident'] ?? ''));
    if ($ident === '') {
      $this->logger->debug('processActiveMember: Member has no ident, skipping FE-User creation', [
        'memberUid' => $memberUid,
      ]);
      return;
    }

    if ($this->hasExistingFeuser($memberUid)) {
      $this->logger->debug('processActiveMember: FE-User already exists', ['memberUid' => $memberUid]);
      return;
    }

    $this->logger->info('processActiveMember: Creating FE-User for member', [
      'memberUid' => $memberUid,
      'ident' => $ident,
    ]);
    $this->createFeuser($record);
  }

  /**
   * Fetch journal entry's member UID directly from DB.
   */
  private function getJournalEntryMemberUid(int $entryUid): ?int
  {
    $queryBuilder = $this->getQueryBuilder('tx_clubmanager_domain_model_memberjournalentry');
    $record = $queryBuilder
      ->select('member')
      ->from('tx_clubmanager_domain_model_memberjournalentry')
      ->where(
        $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($entryUid, Connection::PARAM_INT)),
        $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, Connection::PARAM_INT))
      )
      ->setMaxResults(1)
      ->executeQuery()
      ->fetchAssociative();

    if (is_array($record) && isset($record['member'])) {
      return (int) $record['member'];
    }
    return null;
  }

  /**
   * Hook after ALL DataHandler operations are complete.
   * This runs AFTER ProcessMemberJournalHook has updated member states.
   */
  public function processDatamap_afterAllOperations(DataHandler &$pObj): void
  {
    $memberUids = [];

    // Collect member UIDs from direct member saves
    if (isset($pObj->datamap['tx_clubmanager_domain_model_member'])) {
      foreach ($pObj->datamap['tx_clubmanager_domain_model_member'] as $id => $data) {
        $uid = is_numeric($id) ? (int) $id : ($pObj->substNEWwithIDs[$id] ?? null);
        if ($uid) {
          $memberUids[$uid] = true;
        }
      }
    }

    // Collect member UIDs from journal entry saves (IRRE children)
    if (isset($pObj->datamap['tx_clubmanager_domain_model_memberjournalentry'])) {
      foreach ($pObj->datamap['tx_clubmanager_domain_model_memberjournalentry'] as $id => $data) {
        $memberUid = $data['member'] ?? null;

        // If member UID not in datamap, fetch from DB directly
        $resolvedEntryUid = is_numeric($id) ? (int) $id : ($pObj->substNEWwithIDs[$id] ?? null);
        if ($resolvedEntryUid && $memberUid === null) {
          $memberUid = $this->getJournalEntryMemberUid($resolvedEntryUid);
        }

        if ($memberUid && (int) $memberUid > 0) {
          $memberUids[(int) $memberUid] = true;
        }
      }
    }

    // Process each affected member
    foreach (array_keys($memberUids) as $memberUid) {
      $this->processActiveMember($memberUid);
    }
  }
}
