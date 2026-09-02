<?php

declare(strict_types=1);

namespace Quicko\Clubmanager\EventListener;

use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\TypoScript\IncludeTree\Event\BeforeLoadedPageTsConfigEvent;

#[AsEventListener]
final class AddFeUsersStoragePidPageTsConfigListener
{
  public function __construct(
    private readonly ExtensionConfiguration $extensionConfiguration,
  ) {}

  public function __invoke(BeforeLoadedPageTsConfigEvent $event): void
  {
    $pid = trim((string)$this->extensionConfiguration->get('clubmanager', 'feUsersStoragePid'));
    if ($pid === '') {
      return;
    }
    $event->addTsConfig('TCAdefaults.fe_users.pid = ' . $pid);
  }
}
