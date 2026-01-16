<?php

namespace Quicko\Clubmanager\Hooks;

use Quicko\Clubmanager\Domain\Model\Member;
use Quicko\Clubmanager\Utils\HookUtils;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
   * @param string      $status
   * @param string      $table
   * @param string      $id
   * @param array       $fieldArray
   * @param DataHandler $pObj
   */
  public function processDatamap_afterDatabaseOperations(string &$status, string &$table, string &$id, array &$fieldArray, DataHandler &$pObj): void
  {
    if ($table !== 'tx_clubmanager_domain_model_member') {
      return;
    }
    if ($status !== 'update' && $status !== 'new') {
      return;
    }

    $uid = $id;
    if ($status === 'new') {
      $uid = $pObj->substNEWwithIDs[$id] ?? null;
    }
    if (!$uid) {
      return;
    }

    $record = BackendUtility::getRecord(
      'tx_clubmanager_domain_model_member',
      $uid,
      'uid,state,ident,email'
    );
    if (!is_array($record)) {
      return;
    }
    if ((int) $record['state'] !== Member::STATE_ACTIVE) {
      return;
    }
    if ($this->hasExistingFeuser((int) $record['uid'])) {
      return;
    }

    $this->createFeuser($record);
  }
}
