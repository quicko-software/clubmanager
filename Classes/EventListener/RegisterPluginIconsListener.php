<?php

declare(strict_types=1);

namespace Quicko\Clubmanager\EventListener;

use Quicko\Clubmanager\Utils\PluginRegisterFacade;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Core\Event\BootCompletedEvent;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;

#[AsEventListener(identifier: 'clubmanager/register-plugin-icons')]
final readonly class RegisterPluginIconsListener
{
  public function __invoke(BootCompletedEvent $event): void
  {
    /** @var IconRegistry $iconRegistry */
    $iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);
    PluginRegisterFacade::registerCollectedIcons($iconRegistry);
  }
}
