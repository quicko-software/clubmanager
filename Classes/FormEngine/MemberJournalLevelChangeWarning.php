<?php

namespace Quicko\Clubmanager\FormEngine;

use Quicko\Clubmanager\Domain\Model\Member;
use TYPO3\CMS\Backend\Form\AbstractNode;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Page\JavaScriptModuleInstruction;

/**
 * Renders a config element with dialog configuration for journal entry validations.
 * Used as fieldInformation on the ident field of the member form.
 *
 * Provides JavaScript validation for:
 * - Bug 7: Level change with same old/new level
 * - CR3: Status change to same status as current member
 * - CR5: Status/Level change with past effective date (warning)
 * - CR6: Activation without email address (warning)
 */
class MemberJournalLevelChangeWarning extends AbstractNode
{
  public function render(): array
  {
    $ls = $this->getLanguageService();
    $resultArray = $this->initializeResultArray();
    $resultArray['javaScriptModules'][] = JavaScriptModuleInstruction::create(
      '@quicko/clubmanager/MemberJournalLevelChangeWarning.js'
    );

    // CR5: Warnung bei Vergangenheitsdatum
    $dialogTitle = $ls->sL(
      'LLL:EXT:clubmanager/Resources/Private/Language/locallang_be.xlf:memberjournal.level_change.warning.dialog.title'
    );
    $dialogText = $ls->sL(
      'LLL:EXT:clubmanager/Resources/Private/Language/locallang_be.xlf:memberjournal.level_change.warning.dialog.text'
    );
    $okButtonLabel = $ls->sL(
      'LLL:EXT:clubmanager/Resources/Private/Language/locallang_be.xlf:memberjournal.level_change.warning.dialog.ok-button-label'
    );
    $cancelButtonLabel = $ls->sL(
      'LLL:EXT:clubmanager/Resources/Private/Language/locallang_be.xlf:memberjournal.level_change.warning.dialog.cancel-button-label'
    );

    // Bug 7: Fehler bei gleichem Level
    $sameLevelTitle = $ls->sL(
      'LLL:EXT:clubmanager/Resources/Private/Language/locallang_be.xlf:memberjournal.level_change.same_level.dialog.title'
    );
    $sameLevelText = $ls->sL(
      'LLL:EXT:clubmanager/Resources/Private/Language/locallang_be.xlf:memberjournal.level_change.same_level.dialog.text'
    );

    // CR3: Fehler bei gleichem Status
    $sameStatusTitle = $ls->sL(
      'LLL:EXT:clubmanager/Resources/Private/Language/locallang_be.xlf:memberjournal.status_change.same_status.dialog.title'
    );
    $sameStatusText = $ls->sL(
      'LLL:EXT:clubmanager/Resources/Private/Language/locallang_be.xlf:memberjournal.status_change.same_status.dialog.text'
    );
    $sameStatusPendingCancellationTitle = $ls->sL(
      'LLL:EXT:clubmanager/Resources/Private/Language/locallang_be.xlf:memberjournal.status_change.same_status_pending_cancellation.dialog.title'
    );
    $sameStatusPendingCancellationText = $ls->sL(
      'LLL:EXT:clubmanager/Resources/Private/Language/locallang_be.xlf:memberjournal.status_change.same_status_pending_cancellation.dialog.text'
    );

    // CR6: Warnung bei Aktivierung ohne E-Mail
    $activationNoEmailTitle = $ls->sL(
      'LLL:EXT:clubmanager/Resources/Private/Language/locallang_be.xlf:memberjournal.status_change.no_email.dialog.title'
    );
    $activationNoEmailText = $ls->sL(
      'LLL:EXT:clubmanager/Resources/Private/Language/locallang_be.xlf:memberjournal.status_change.no_email.dialog.text'
    );

    // Aktuellen Member-Status aus dem Formular-Daten holen
    $memberState = $this->data['databaseRow']['state'] ?? 0;

    // Render a hidden span element that carries the dialog configuration
    $resultArray['html'] = sprintf(
      '<span class="clbmgr_member-journal-level-warning" style="display:none" ' .
      'data-dialog-title="%s" data-dialog-text="%s" ' .
      'data-dialog-ok-button-label="%s" data-dialog-cancel-button-label="%s" ' .
      'data-same-level-title="%s" data-same-level-text="%s" ' .
      'data-same-status-title="%s" data-same-status-text="%s" ' .
      'data-same-status-pending-cancellation-title="%s" data-same-status-pending-cancellation-text="%s" ' .
      'data-no-email-title="%s" data-no-email-text="%s" ' .
      'data-member-state="%d" data-active-state="%d" data-cancelled-state="%d"></span>',
      htmlspecialchars($dialogTitle, ENT_QUOTES),
      htmlspecialchars($dialogText, ENT_QUOTES),
      htmlspecialchars($okButtonLabel, ENT_QUOTES),
      htmlspecialchars($cancelButtonLabel, ENT_QUOTES),
      htmlspecialchars($sameLevelTitle, ENT_QUOTES),
      htmlspecialchars($sameLevelText, ENT_QUOTES),
      htmlspecialchars($sameStatusTitle, ENT_QUOTES),
      htmlspecialchars($sameStatusText, ENT_QUOTES),
      htmlspecialchars($sameStatusPendingCancellationTitle, ENT_QUOTES),
      htmlspecialchars($sameStatusPendingCancellationText, ENT_QUOTES),
      htmlspecialchars($activationNoEmailTitle, ENT_QUOTES),
      htmlspecialchars($activationNoEmailText, ENT_QUOTES),
      (int) $memberState,
      Member::STATE_ACTIVE,
      Member::STATE_CANCELLED
    );

    return $resultArray;
  }

  protected function getLanguageService(): LanguageService
  {
    return $GLOBALS['LANG'];
  }
}
