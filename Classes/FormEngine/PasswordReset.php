<?php

namespace Quicko\Clubmanager\FormEngine;

use TYPO3\CMS\Backend\Form\AbstractNode;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Page\JavaScriptModuleInstruction;

class PasswordReset extends AbstractNode
{
  public const RESET_VALUE_TOKEN = '###AUTO_ReSET_PaSSWoRD_123_%###';

  public function render(): array
  {
    $ls = $this->getLanguageService();
    $resultArray = $this->initializeResultArray();
    $resultArray['iconIdentifier'] = 'icon_password-reset';
    // $resultArray['javaScriptModules'][] = JavaScriptModuleInstruction::create('@quicko/clubmanager/PasswordReset.js');
    $resultArray['javaScriptModules'] = [
                JavaScriptModuleInstruction::create('@quicko/clubmanager/PasswordReset.js')->instance(),
    ];
    $resultArray['title'] = $ls->sL(
      'LLL:EXT:clubmanager/Resources/Private/Language/locallang_be.xlf:password-reset.button.title'
    );
    $resultArray['linkAttributes']['class'] = 'clbmgr_password-reset';
    $resultArray['linkAttributes']['data-formengine-input-name'] = 'data' . $this->data['elementBaseName'];
    $resultArray['linkAttributes']['data-reset-value'] = self::RESET_VALUE_TOKEN;

    return $resultArray;
  }

  protected function getLanguageService(): LanguageService
  {
    return $GLOBALS['LANG'];
  }
}
