<?php

namespace Quicko\Clubmanager\FormEngine;

use TYPO3\CMS\Backend\Form\Element\InputTextElement;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Page\JavaScriptModuleInstruction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;

class SearchLocationButton extends InputTextElement
{
  /**
   * @return array As defined in initializeResultArray() of AbstractNode
   */
  public function render(): array
  {
    $array = $this->mergeChildReturnIntoExistingResult(
      $this->initializeResultArray(),
      $this->renderFieldInformation(),
      false
    );

    $mapping = $this->data['parameterArray']['fieldConf']['config']['mapping'];
    $target = $this->data['parameterArray']['fieldConf']['config']['target'];
    // @deprecated since v12, will be removed with v13 when all elements handle label/legend on their own
    $array['labelHasBeenHandled'] = true;
    $fieldId = StringUtility::getUniqueId('formengine-input-');

    $renderedLabel = $this->renderLabel($fieldId);

    /** @var IconFactory $iconFactory */
    $iconFactory = GeneralUtility::makeInstance(IconFactory::class);
    //    'data-mapping' => json_encode($mapping),
    $array['html'] = sprintf(
      '%s<div class="clbmgr_search_location"><a href="#" class="clbmgr_search_location_button" data-mapping=\'%s\' data-target=\'%s\' data-name=\'%s\'  data-uid=\'%s\' data-fieldName=\'%s\' data-tableName=\'%s\' >%s</a></div>',
      $renderedLabel,
      json_encode($mapping),
      json_encode($target),
      $this->data['parameterArray']['itemFormElName'],
      $this->data['vanillaUid'],
      $this->data['fieldName'],
      $this->data['tableName'],
      $iconFactory->getIcon('apps-toolbar-menu-search')
    );

    $array['javaScriptModules'][] = JavaScriptModuleInstruction::create('@quicko/clubmanager/SearchLocation.js');
    $array['stylesheetFiles'][] = 'EXT:clubmanager/Resources/Public/Css/Backend.css';

    return $array;
  }
}
