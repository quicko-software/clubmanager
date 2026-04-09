<?php

namespace Quicko\Clubmanager\EventListener;

use Psr\Log\LoggerInterface;
use Quicko\Clubmanager\Domain\Model\Mail\Task;
use Quicko\Clubmanager\Domain\Model\Member;
use Quicko\Clubmanager\Event\MemberStateChangedEvent;
use Quicko\Clubmanager\Mail\Generator\Arguments\PasswordRecoveryArguments;
use Quicko\Clubmanager\Mail\Generator\PasswordRecoveryGenerator;
use Quicko\Clubmanager\Mail\MailQueue;
use Quicko\Clubmanager\Utils\PasswordGenerator;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Creates an FE user when a member is activated via CLI/cron
 * (MemberStateChangedEvent).
 *
 * Uses ConnectionPool directly (no DataHandler) to avoid permission
 * issues in CLI context. Mirrors the logic of MemberAutoCreateFeuserHook
 * (backend path) and ResetFeuserPasswordHook (password + login mail).
 */
#[AsEventListener(identifier: 'clubmanager/frontend-user-auto-create')]
final readonly class FrontendUserAutoCreateListener
{
  public function __construct(
    private LoggerInterface $logger,
    private ExtensionConfiguration $extConf,
  ) {
  }

  public function __invoke(MemberStateChangedEvent $event): void
  {
    if ($event->getNewState() !== Member::STATE_ACTIVE) {
      return;
    }

    $memberUid = $event->getMemberUid();

    $record = $this->getMemberRecord($memberUid);
    if ($record === null) {
      return;
    }

    $ident = trim((string) ($record['ident'] ?? ''));
    if ($ident === '') {
      return;
    }

    if ($this->hasExistingFeuser($memberUid)) {
      return;
    }

    $storagePid = (int) $this->extConf->get('clubmanager', 'feUsersStoragePid');
    if ($storagePid <= 0) {
      $this->logger->error('Cannot create fe_user: feUsersStoragePid is not configured.');
      return;
    }

    $username = $this->resolveUsername($record);
    $password = PasswordGenerator::generatePassword();
    $hashedPassword = GeneralUtility::makeInstance(PasswordHashFactory::class)
      ->getDefaultHashInstance('FE')
      ->getHashedPassword($password);

    $userGroup = trim((string) $this->extConf->get('clubmanager', 'defaultFeUserGroupUid'));

    $insertData = [
      'pid' => $storagePid,
      'username' => $username,
      'email' => (string) ($record['email'] ?? ''),
      'password' => $hashedPassword,
      'clubmanager_member' => $memberUid,
      'crdate' => time(),
      'tstamp' => time(),
    ];

    if ($userGroup !== '') {
      $insertData['usergroup'] = $userGroup;
    }

    $connection = GeneralUtility::makeInstance(ConnectionPool::class)
      ->getConnectionForTable('fe_users');
    $connection->insert('fe_users', $insertData);

    $this->logger->info('Created fe_user for member via CLI activation', [
      'memberUid' => $memberUid,
      'username' => $username,
    ]);

    $this->pushLoginDataMail($memberUid);
  }

  private function pushLoginDataMail(int $memberUid): void
  {
    $args = new PasswordRecoveryArguments();
    $args->memberUid = $memberUid;
    $args->templateName = 'Logindata';
    MailQueue::addMailTask(PasswordRecoveryGenerator::class, $args, Task::PRIORITY_LEVEL_MEDIUM);
  }

  private function getMemberRecord(int $memberUid): ?array
  {
    $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
      ->getQueryBuilderForTable('tx_clubmanager_domain_model_member');
    $queryBuilder->getRestrictions()->removeAll();

    $record = $queryBuilder
      ->select('uid', 'ident', 'email')
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

  private function hasExistingFeuser(int $memberUid): bool
  {
    $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
      ->getQueryBuilderForTable('fe_users');
    $queryBuilder->getRestrictions()->removeAll();

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
    $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
      ->getQueryBuilderForTable('fe_users');
    $queryBuilder->getRestrictions()->removeAll();

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
}
