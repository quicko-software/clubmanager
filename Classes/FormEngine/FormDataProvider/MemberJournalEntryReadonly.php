<?php

declare(strict_types=1);

namespace Quicko\Clubmanager\FormEngine\FormDataProvider;

use TYPO3\CMS\Backend\Form\FormDataProviderInterface;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

/**
 * Sets all editable fields of MemberJournalEntry records to readOnly
 * when creator_type equals 0 (System) or 2 (Member). Admins can always edit.
 * Also ensures creator_type is editable for admins on new records.
 */
class MemberJournalEntryReadonly implements FormDataProviderInterface
{
  private const CREATOR_TYPE_SYSTEM = 0;
  private const CREATOR_TYPE_MEMBER = 2;

  public function addData(array $result): array
  {
    if ($result['tableName'] !== 'tx_clubmanager_domain_model_memberjournalentry') {
      return $result;
    }

    $isAdmin = $this->getBackendUser()->isAdmin();

    // For admins: Make creator_type editable (remove readOnly set in TCA)
    if ($isAdmin && isset($result['processedTca']['columns']['creator_type']['config']['readOnly'])) {
      unset($result['processedTca']['columns']['creator_type']['config']['readOnly']);
    }

    // For new records: Set entry_date to current timestamp
    if ($result['command'] === 'new') {
      $result['databaseRow']['entry_date'] = time();
      return $result;
    }

    // Only apply readOnly for edit command
    if ($result['command'] !== 'edit') {
      return $result;
    }

    // Admins can always edit
    if ($isAdmin) {
      return $result;
    }

    $creatorTypeRaw = $result['databaseRow']['creator_type'] ?? -1;
    $creatorType = is_array($creatorTypeRaw) ? (int) ($creatorTypeRaw[0] ?? -1) : (int) $creatorTypeRaw;

    // Allow editing only for Backend-created entries (creator_type = 1)
    if ($creatorType !== self::CREATOR_TYPE_SYSTEM && $creatorType !== self::CREATOR_TYPE_MEMBER) {
      return $result;
    }

    // Set all columns to readOnly for system/member-created entries
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
