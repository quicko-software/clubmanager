<?php

namespace Quicko\Clubmanager\ViewHelpers;

//
// Simplifies returning the result of a view helper:
// If an argument 'as' is given, it writes it back to the view
// the with 'as' specified variable name and returns the children's rendering.
// Otherwise, it returns the result directly.
//
trait ReturnResultTrait {
  
  private function registerReturnAsArgument() {
    $this->registerArgument(
      'as',
      'string',
      'Template variable name to assign; if not specified the ViewHelper returns the variable instead.',
      true
    );
  }

  private function returnResult($result) {
    $as = $this->arguments['as'];
    if (! $as) {
      return $result;
    }

    if ($this->templateVariableContainer->exists($as) === TRUE) {
      $this->templateVariableContainer->remove($as);
    }
    $this->templateVariableContainer->add($as, $result);
    
    return $this->renderChildren();
  }
}
