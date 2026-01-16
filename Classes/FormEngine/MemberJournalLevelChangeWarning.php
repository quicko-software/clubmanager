<?php

namespace Quicko\Clubmanager\FormEngine;

use TYPO3\CMS\Backend\Form\AbstractNode;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Page\JavaScriptModuleInstruction;

/**
 * Renders a config element with dialog configuration for the level-change warning.
 * Used as fieldInformation on the ident field of the member form.
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

    // Render a hidden span element that carries the dialog configuration
    $resultArray['html'] = sprintf(
      '<span class="clbmgr_member-journal-level-warning" style="display:none" data-dialog-title="%s" data-dialog-text="%s" data-dialog-ok-button-label="%s" data-dialog-cancel-button-label="%s"></span>',
      htmlspecialchars($dialogTitle, ENT_QUOTES),
      htmlspecialchars($dialogText, ENT_QUOTES),
      htmlspecialchars($okButtonLabel, ENT_QUOTES),
      htmlspecialchars($cancelButtonLabel, ENT_QUOTES)
    );

    return $resultArray;
  }

  protected function getLanguageService(): LanguageService
  {
    return $GLOBALS['LANG'];
  }
}
