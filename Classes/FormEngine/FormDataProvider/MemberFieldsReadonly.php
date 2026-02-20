<?php

declare(strict_types=1);

namespace Quicko\Clubmanager\FormEngine\FormDataProvider;

use TYPO3\CMS\Backend\Form\FormDataProviderInterface;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

/**
 * Sets fields to readOnly for Members:
 * - level: Always readOnly (changes only through Journal entries)
 * - ident: ReadOnly for non-admins after first activation (starttime is set)
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

    // CR1: ident nach erster Aktivierung für Redakteure sperren
    // starttime wird bei Aktivierung gesetzt und dient als Indikator
    if (!$this->getBackendUser()->isAdmin()) {
      $starttime = $result['databaseRow']['starttime'] ?? null;
      $wasActivated = $this->wasEverActivated($starttime);

      if ($wasActivated && isset($result['processedTca']['columns']['ident'])) {
        $result['processedTca']['columns']['ident']['config']['readOnly'] = true;
      }
    }

    return $result;
  }

  /**
   * Prüft ob das Mitglied bereits einmal aktiviert war.
   * starttime wird bei der ersten Aktivierung gesetzt.
   */
  private function wasEverActivated(mixed $starttime): bool
  {
    if ($starttime === null || $starttime === '' || $starttime === 0 || $starttime === '0') {
      return false;
    }

    // starttime kann als Timestamp (int) oder formatierter String kommen
    if (is_numeric($starttime)) {
      return (int) $starttime > 0;
    }

    // Formatierter String (z.B. "2024-01-15 00:00:00")
    return trim((string) $starttime) !== '';
  }

  private function getBackendUser(): BackendUserAuthentication
  {
    return $GLOBALS['BE_USER'];
  }
}
