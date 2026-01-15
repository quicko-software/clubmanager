<?php

declare(strict_types=1);

namespace Quicko\Clubmanager\FormEngine\FormDataProvider;

use TYPO3\CMS\Backend\Form\FormDataProviderInterface;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

/**
 * Disables the delete control for journal entries for non-admin users.
 * Only admins can delete journal entries to protect the audit trail.
 */
final class JournalEntryDeleteControl implements FormDataProviderInterface
{
  public function addData(array $result): array
  {
    if ($result['tableName'] !== 'tx_clubmanager_domain_model_member') {
      return $result;
    }

    // Only restrict for non-admins
    if ($this->getBackendUser()->isAdmin()) {
      return $result;
    }

    // Disable delete control for journal_entries inline field
    if (isset($result['processedTca']['columns']['journal_entries']['config']['appearance'])) {
      $result['processedTca']['columns']['journal_entries']['config']['appearance']['enabledControls']['delete'] = false;
    }

    return $result;
  }

  private function getBackendUser(): BackendUserAuthentication
  {
    return $GLOBALS['BE_USER'];
  }
}
