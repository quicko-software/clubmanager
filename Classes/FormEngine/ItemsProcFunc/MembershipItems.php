<?php

declare(strict_types=1);

namespace Quicko\Clubmanager\FormEngine\ItemsProcFunc;

use Quicko\Clubmanager\Service\MembershipMasterDataService;
use TYPO3\CMS\Backend\Utility\BackendUtility;

/**
 * Auswahllisten für Mitgliedschaftstyp und Level im Journal (#6425).
 *
 * Angeboten werden nur wählbare Datensätze (nicht deaktiviert, Standardsprache, ggf. nur
 * aus den Seiten im Site-Setting `clubmanager.membershipMasterDataPids`). Level erscheinen
 * nur, wenn sie dem gewählten Typ zugeordnet sind. Ein bereits gespeicherter Wert bleibt
 * sichtbar, auch wenn er inzwischen deaktiviert ist.
 */
final class MembershipItems
{
  private const MEMBER_TABLE = 'tx_clubmanager_domain_model_member';

  public function __construct(
    private readonly MembershipMasterDataService $masterData,
  ) {}

  /**
   * @param array<string, mixed> $params
   */
  public function types(array &$params): void
  {
    $rows = $this->masterData->selectableTypes($this->contextPid($params));
    $params['items'] = $this->buildItems($rows, $params, MembershipMasterDataService::TABLE_TYPE);
    // Option `emptyLabel`: Beschriftung des leeren Eintrags (z. B. „Alle Typen“).
    $emptyLabel = (string)($params['config']['itemsProcConfig']['emptyLabel'] ?? '');
    if ($emptyLabel !== '') {
      $params['items'][0]['label'] = $emptyLabel;
    }
  }

  /**
   * @param array<string, mixed> $params
   */
  public function levels(array &$params): void
  {
    $row = is_array($params['row'] ?? null) ? $params['row'] : [];
    $typeField = (string)($params['config']['itemsProcConfig']['typeField'] ?? 'new_membership_type');
    $typeUid = self::firstUid($row[$typeField] ?? 0);
    // Option `allLevelsWithoutType`: ohne Typ alle wählbaren Level (z. B. Beitragssatz „alle Typen“).
    $rows = $typeUid <= 0 && !empty($params['config']['itemsProcConfig']['allLevelsWithoutType'])
      ? $this->masterData->selectableLevelsOfAnyType($this->contextPid($params))
      : $this->masterData->selectableLevels($typeUid, $this->contextPid($params));
    $params['items'] = $this->buildItems($rows, $params, MembershipMasterDataService::TABLE_LEVEL);
  }

  /**
   * @param list<array{uid: int, title: string}> $rows
   * @param array<string, mixed> $params
   * @return list<array{label: string, value: int}>
   */
  private function buildItems(array $rows, array $params, string $table): array
  {
    $items = [['label' => '', 'value' => 0]];
    $known = [];
    foreach ($rows as $row) {
      $items[] = ['label' => $row['title'], 'value' => $row['uid']];
      $known[$row['uid']] = true;
    }

    $row = is_array($params['row'] ?? null) ? $params['row'] : [];
    $current = self::firstUid($row[(string)($params['field'] ?? '')] ?? 0);
    if ($current > 0 && !isset($known[$current])) {
      $record = BackendUtility::getRecord($table, $current, 'title');
      if (is_array($record)) {
        $items[] = ['label' => (string)$record['title'], 'value' => $current];
      }
    }

    return $items;
  }

  /**
   * Seite für das Site-Setting: Ordner des Mitglieds, sonst die effektive Seite des Eintrags.
   *
   * @param array<string, mixed> $params
   */
  private function contextPid(array $params): int
  {
    $row = is_array($params['row'] ?? null) ? $params['row'] : [];
    $memberUid = self::firstUid($row['member'] ?? 0);
    if ($memberUid > 0) {
      $member = BackendUtility::getRecord(self::MEMBER_TABLE, $memberUid, 'pid');
      if ((int)($member['pid'] ?? 0) > 0) {
        return (int)$member['pid'];
      }
    }
    $inlineParentUid = self::firstUid($params['inlineParentUid'] ?? 0);
    if (($params['inlineParentTableName'] ?? '') === self::MEMBER_TABLE && $inlineParentUid > 0) {
      $member = BackendUtility::getRecord(self::MEMBER_TABLE, $inlineParentUid, 'pid');
      if ((int)($member['pid'] ?? 0) > 0) {
        return (int)$member['pid'];
      }
    }

    return max(0, (int)($params['effectivePid'] ?? 0));
  }

  /**
   * FormEngine liefert Relationswerte als Zahl, String oder Liste.
   */
  private static function firstUid(mixed $value): int
  {
    if (is_array($value)) {
      $value = reset($value);
      if (is_array($value)) {
        $value = $value['uid'] ?? 0;
      }
    }
    if (is_string($value) && preg_match('/(\d+)$/', $value, $matches) === 1) {
      return (int)$matches[1];
    }

    return is_int($value) ? $value : 0;
  }
}
