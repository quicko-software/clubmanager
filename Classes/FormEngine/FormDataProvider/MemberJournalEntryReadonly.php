<?php

declare(strict_types=1);

namespace Quicko\Clubmanager\FormEngine\FormDataProvider;

use TYPO3\CMS\Backend\Form\FormDataProviderInterface;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

/**
 * Sets fields of MemberJournalEntry records to readOnly:
 * - creator_type and entry_date: ReadOnly for non-admins (admins can edit for corrections)
 * - All fields: ReadOnly when processed is set (for everyone), or when creator_type is System/Member (non-admins only)
 */
final class MemberJournalEntryReadonly implements FormDataProviderInterface
{
  private const CREATOR_TYPE_SYSTEM = 0;
  private const CREATOR_TYPE_MEMBER = 2;

  public function addData(array $result): array
  {
    if ($result['tableName'] !== 'tx_clubmanager_domain_model_memberjournalentry') {
      return $result;
    }

    $isAdmin = $this->getBackendUser()->isAdmin();

    // For new records: Set entry_date to current timestamp for display
    // Note: The actual value will be set by AutoFillJournalEntryFieldsHook on save
    if ($result['command'] === 'new') {
      $result['databaseRow']['entry_date'] = time();
    }

    // old_level and processed are ALWAYS readOnly (even for admins)
    // These are system-determined values
    $systemFields = ['old_level', 'processed'];
    foreach ($systemFields as $fieldName) {
      if (isset($result['processedTca']['columns'][$fieldName])) {
        $result['processedTca']['columns'][$fieldName]['config']['readOnly'] = true;
      }
    }

    // For non-admins: additional fields are also readOnly
    // Admins can edit these fields for corrections
    if (!$isAdmin) {
      $nonAdminReadOnlyFields = ['creator_type', 'entry_date'];
      foreach ($nonAdminReadOnlyFields as $fieldName) {
        if (isset($result['processedTca']['columns'][$fieldName])) {
          $result['processedTca']['columns'][$fieldName]['config']['readOnly'] = true;
        }
      }
    }

    // Only apply additional readOnly logic for edit command
    if ($result['command'] !== 'edit') {
      return $result;
    }

    // Check if entry has been processed
    $processedRaw = $result['databaseRow']['processed'] ?? null;
    $isProcessed = !empty($processedRaw) && ((is_array($processedRaw) ? (int) ($processedRaw[0] ?? 0) : (int) $processedRaw) > 0);

    // Check creator type
    $creatorTypeRaw = $result['databaseRow']['creator_type'] ?? -1;
    $creatorType = is_array($creatorTypeRaw) ? (int) ($creatorTypeRaw[0] ?? -1) : (int) $creatorTypeRaw;

    // Determine if all fields should be readOnly
    // Processed entries are always readOnly for everyone (except admins)
    // System/Member entries are readOnly for non-admins only
    $shouldBeReadOnly = (!$isAdmin && $isProcessed) ||
      (!$isAdmin && ($creatorType === self::CREATOR_TYPE_SYSTEM || $creatorType === self::CREATOR_TYPE_MEMBER));

    if (!$shouldBeReadOnly) {
      return $result;
    }

    // Set all columns to readOnly
    foreach (array_keys($result['processedTca']['columns']) as $fieldName) {
      if (($result['processedTca']['columns'][$fieldName]['config']['type'] ?? '') === 'passthrough') {
        continue;
      }
      $result['processedTca']['columns'][$fieldName]['config']['readOnly'] = true;
    }

    return $result;
  }

  private function getBackendUser(): BackendUserAuthentication
  {
    return $GLOBALS['BE_USER'];
  }
}
