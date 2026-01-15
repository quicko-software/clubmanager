<?php

declare(strict_types=1);

namespace Quicko\Clubmanager\FormEngine\FormDataProvider;

use TYPO3\CMS\Backend\Form\FormDataProviderInterface;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

/**
 * Sets the level field to readOnly for Members.
 * Level changes should only be made through Journal entries.
 * This applies to ALL users including admins - level is determined by journal data.
 */
final class MemberFieldsReadonly implements FormDataProviderInterface
{
  public function addData(array $result): array
  {
    if ($result['tableName'] !== 'tx_clubmanager_domain_model_member') {
      return $result;
    }

    // Set level field to readOnly for ALL users (including admins)
    // Level is determined by journal entries and processed by commands/tasks
    if (isset($result['processedTca']['columns']['level'])) {
      $result['processedTca']['columns']['level']['config']['readOnly'] = true;
    }

    return $result;
  }

  private function getBackendUser(): BackendUserAuthentication
  {
    return $GLOBALS['BE_USER'];
  }
}
