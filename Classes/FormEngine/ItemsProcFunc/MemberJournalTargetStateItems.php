<?php

namespace Quicko\Clubmanager\FormEngine\ItemsProcFunc;

use Quicko\Clubmanager\Domain\Model\Member;
use TYPO3\CMS\Core\Schema\Struct\SelectItem;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

class MemberJournalTargetStateItems
{
  /**
   * @param array<string,mixed> $params
   */
  public function build(array &$params): void
  {
    $currentValue = $this->resolveCurrentTargetState($params);
    $originalItems = $params['items'] ?? [];
    $filteredItems = [];
    $hadAppliedBeforeFilter = false;
    $hadCancelledBeforeFilter = false;
    $isBillingLoaded = ExtensionManagementUtility::isLoaded('clubmanager_billing');

    foreach ($originalItems as $item) {
      $value = $this->extractItemValue($item);
      if ($value === Member::STATE_APPLIED) {
        $hadAppliedBeforeFilter = true;
        // Nur bei Legacy-Datensatz mit bereits gesetztem applied anzeigen.
        if ($currentValue === Member::STATE_APPLIED) {
          $filteredItems[] = $item;
        }
        continue;
      }
      if ($value === Member::STATE_CANCELLED && $isBillingLoaded) {
        $hadCancelledBeforeFilter = true;
        // Nur bei Legacy-Datensatz mit bereits gesetztem cancelled anzeigen.
        if ($currentValue === Member::STATE_CANCELLED) {
          $filteredItems[] = $item;
        }
        continue;
      }
      $filteredItems[] = $item;
    }

    // Falls TYPO3 den Legacy-Wert nicht als Item liefert, explizit anzeigen (nur für bestehende applied-Datensaetze).
    if ($currentValue === Member::STATE_APPLIED && !$hadAppliedBeforeFilter) {
      array_unshift($filteredItems, [
        'label' => $this->resolveLabel(
          'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.state.1',
          'Requested'
        ),
        'value' => Member::STATE_APPLIED,
      ]);
    }
    if ($isBillingLoaded && $currentValue === Member::STATE_CANCELLED && !$hadCancelledBeforeFilter) {
      $filteredItems[] = [
        'label' => $this->resolveLabel(
          'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.state.4',
          'Canceled'
        ),
        'value' => Member::STATE_CANCELLED,
      ];
    }
    $params['items'] = $filteredItems;
  }

  private function extractItemValue(mixed $item): int
  {
    if ($item instanceof SelectItem) {
      return (int) $item->getValue();
    }
    if (is_array($item)) {
      return (int) ($item['value'] ?? -9999);
    }

    return -9999;
  }

  /**
   * @param array<string,mixed> $params
   */
  private function resolveCurrentTargetState(array $params): int
  {
    $candidates = [
      $params['itemFormElValue'] ?? null,
      $params['row']['target_state'] ?? null,
      $params['databaseRow']['target_state'] ?? null,
    ];

    foreach ($candidates as $value) {
      if (is_numeric($value)) {
        return (int) $value;
      }
      if (is_array($value) && isset($value[0]) && is_numeric($value[0])) {
        return (int) $value[0];
      }
    }

    return -9999;
  }

  private function resolveLabel(string $labelKey, string $fallback): string
  {
    $languageService = $GLOBALS['LANG'] ?? null;
    if (is_object($languageService) && method_exists($languageService, 'sL')) {
      $resolved = (string) $languageService->sL($labelKey);
      if ($resolved !== '' && !str_starts_with($resolved, 'LLL:')) {
        return $resolved;
      }
    }

    return $fallback;
  }
}
