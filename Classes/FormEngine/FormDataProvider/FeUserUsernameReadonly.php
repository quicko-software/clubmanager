<?php

declare(strict_types=1);

namespace Quicko\Clubmanager\FormEngine\FormDataProvider;

use TYPO3\CMS\Backend\Form\FormDataProviderInterface;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

/**
 * Sets fe_users.username to readOnly for non-admin editors
 * when the FE user is linked to a clubmanager member.
 */
final class FeUserUsernameReadonly implements FormDataProviderInterface
{
  public function addData(array $result): array
  {
    if ($result['tableName'] !== 'fe_users') {
      return $result;
    }

    if ($this->getBackendUser()->isAdmin()) {
      return $result;
    }

    $clubmanagerMember = $result['databaseRow']['clubmanager_member'] ?? null;
    $memberUid = is_array($clubmanagerMember)
      ? (int) ($clubmanagerMember[0] ?? 0)
      : (int) $clubmanagerMember;

    if ($memberUid <= 0) {
      return $result;
    }

    if (isset($result['processedTca']['columns']['username'])) {
      $result['processedTca']['columns']['username']['config']['readOnly'] = true;
    }

    return $result;
  }

  private function getBackendUser(): BackendUserAuthentication
  {
    return $GLOBALS['BE_USER'];
  }
}
