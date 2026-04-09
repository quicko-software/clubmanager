<?php

namespace Quicko\Clubmanager\EventListener;

use Psr\Log\LoggerInterface;
use Quicko\Clubmanager\Domain\Model\Member;
use Quicko\Clubmanager\Event\MemberStateChangedEvent;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

#[AsEventListener(identifier: 'clubmanager/frontend-user-sync')]
final readonly class FrontendUserSyncListener
{
  public function __construct(
    private LoggerInterface $logger,
  ) {
  }

  public function __invoke(MemberStateChangedEvent $event): void
  {
    $shouldDisable = ($event->getNewState() !== Member::STATE_ACTIVE);

    $connection = GeneralUtility::makeInstance(ConnectionPool::class)
      ->getConnectionForTable('fe_users');

    $affectedRows = $connection->update(
      'fe_users',
      ['disable' => $shouldDisable ? 1 : 0],
      [
        'clubmanager_member' => $event->getMemberUid(),
        'deleted' => 0,
      ]
    );

    if ($affectedRows > 0) {
      $this->logger->info(
        sprintf(
          'Updated fe_users disable=%d for member %d (%d rows)',
          $shouldDisable ? 1 : 0,
          $event->getMemberUid(),
          $affectedRows
        )
      );
    }
  }
}
