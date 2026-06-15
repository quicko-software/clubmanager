<?php

declare(strict_types=1);

namespace Quicko\Clubmanager\FormEngine\FormDataProvider;

use TYPO3\CMS\Backend\Form\FormDataProviderInterface;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

/**
 * Hides the "create new record" button of the feuser inline relation for
 * non-admin users. The FE user is created automatically via the member
 * journal, so editors must not create one manually (single source of truth).
 * Admins keep the button to allow manual handling when required.
 */
final class FeUserNewButtonAdminOnly implements FormDataProviderInterface
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

    // Hide the "new" control and the "create new record" link for the feuser inline field
    if (isset($result['processedTca']['columns']['feuser']['config'])) {
      $result['processedTca']['columns']['feuser']['config']['appearance']['enabledControls']['new'] = false;
      $result['processedTca']['columns']['feuser']['config']['appearance']['showNewRecordLink'] = false;
    }

    return $result;
  }

  private function getBackendUser(): BackendUserAuthentication
  {
    return $GLOBALS['BE_USER'];
  }
}
