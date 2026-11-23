<?php

declare(strict_types=1);

namespace Quicko\Clubmanager\Hooks;

use Quicko\Clubmanager\Service\MembershipMasterDataService;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;

/**
 * Neue Mitglieder bekommen Mitgliedschaftstyp und Level aus den Site-Settings (#6425).
 *
 * Greift nur bei neuen Datensätzen ohne Typ; danach ändern sich Typ und Level nur über das
 * Journal (E4). Gesetzt werden auch `person_type` und im Übergang `level` (Rang).
 */
final class MemberMembershipDefaultsHook
{
  private const TABLE = 'tx_clubmanager_domain_model_member';

  public function __construct(
    private readonly MembershipMasterDataService $masterData,
  ) {}

  /**
   * @param array<string, mixed> $fieldArray
   */
  public function processDatamap_preProcessFieldArray(
    array &$fieldArray,
    string $table,
    string|int $id,
    DataHandler $dataHandler
  ): void {
    if ($table !== self::TABLE || is_numeric($id) || (int)($fieldArray['membership_type'] ?? 0) > 0) {
      return;
    }
    $defaults = $this->masterData->defaultsForNewMember($this->resolvePid((int)($fieldArray['pid'] ?? 0)));
    foreach ($defaults as $field => $value) {
      $fieldArray[$field] = $value;
    }
  }

  /**
   * Negative pid heißt „nach Datensatz uid“: dessen Seite gilt.
   */
  private function resolvePid(int $pid): int
  {
    if ($pid >= 0) {
      return $pid;
    }
    $record = BackendUtility::getRecord(self::TABLE, abs($pid), 'pid');

    return (int)($record['pid'] ?? 0);
  }
}
