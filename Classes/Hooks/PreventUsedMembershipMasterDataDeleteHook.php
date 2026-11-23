<?php

declare(strict_types=1);

namespace Quicko\Clubmanager\Hooks;

use Quicko\Clubmanager\Service\MembershipMasterDataUsageService;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\SysLog\Action\Database as SystemLogDatabaseAction;
use TYPO3\CMS\Core\SysLog\Error as SystemLogErrorClassification;

/**
 * Löschschutz für Mitgliedschaftstypen und Level (#6425, E10).
 *
 * Verwendete Datensätze dürfen nicht gelöscht werden, sondern nur deaktiviert (`inactive`).
 * Geprüft wird das Löschen des Datensatzes selbst und das Löschen einer Seite, auf der
 * (oder unterhalb derer) ein verwendeter Datensatz liegt. Die Meldung erscheint als
 * DataHandler-Fehler (Sprache des Backend-Benutzers), also auch beim Löschen aus der Listenansicht.
 */
final class PreventUsedMembershipMasterDataDeleteHook
{
  public function __construct(
    private readonly MembershipMasterDataUsageService $usageService,
  ) {}

  public function processCmdmap(
    string $command,
    string $table,
    int|string $id,
    mixed $value,
    bool &$commandIsProcessed,
    DataHandler $dataHandler,
    mixed $pasteUpdate = false
  ): void {
    if ($command !== 'delete' || $commandIsProcessed || !is_numeric($id)) {
      return;
    }
    $uid = (int)$id;

    if (MembershipMasterDataUsageService::isMasterDataTable($table)) {
      $usages = $this->usageService->usages($table, $uid);
      if ($usages === []) {
        return;
      }
      $commandIsProcessed = true;
      $this->logBlocked($dataHandler, $table, $uid, self::translate(
        'flash.masterdata_delete_blocked',
        'Record cannot be deleted because it is still in use (%s). Deactivate it instead.',
        [self::formatUsages($usages)]
      ));

      return;
    }

    if ($table === 'pages') {
      $used = $this->usageService->usedRecordsInPageTree($uid);
      if ($used === []) {
        return;
      }
      $commandIsProcessed = true;
      $titles = implode(', ', array_map(static fn(array $record): string => $record['title'] . ' [' . $record['uid'] . ']', $used));
      $this->logBlocked($dataHandler, 'pages', $uid, self::translate(
        'flash.masterdata_page_delete_blocked',
        'Page cannot be deleted because it contains membership types or levels in use: %s. Deactivate or move them first.',
        [$titles]
      ));
    }
  }

  /**
   * @param array<string, int> $usages
   */
  private static function formatUsages(array $usages): string
  {
    $parts = [];
    foreach ($usages as $label => $count) {
      $parts[] = $label . ': ' . $count;
    }

    return implode(', ', $parts);
  }

  private function logBlocked(DataHandler $dataHandler, string $table, int $uid, string $message): void
  {
    $dataHandler->log($table, $uid, SystemLogDatabaseAction::DELETE, null, SystemLogErrorClassification::USER_ERROR, $message);
  }

  /**
   * @param list<string> $arguments
   */
  private static function translate(string $key, string $fallback, array $arguments): string
  {
    $languageService = $GLOBALS['LANG'] ?? null;
    $pattern = $languageService instanceof LanguageService
      ? ($languageService->sL('LLL:EXT:clubmanager/Resources/Private/Language/locallang_be.xlf:' . $key) ?: $fallback)
      : $fallback;

    return vsprintf($pattern, $arguments);
  }
}
