<?php

namespace Quicko\Clubmanager\ViewHelpers;

//
// Simplifies returning the result of a view helper:
// If an argument 'as' is given, it writes it back to the view
// the with 'as' specified variable name and returns the children's rendering.
// Otherwise, it returns the result directly.
//
trait ReturnResultTrait
{
  private function registerReturnAsArgument(): void
  {
    $this->registerArgument(
      'as',
      'string',
      'Template variable name to assign; if not specified the ViewHelper returns the variable instead.',
      true
    );
  }

  private function returnResult(mixed $result): mixed
  {
    $as = $this->arguments['as'];
    $variables = $this->templateVariableContainer;
    if (!$as || $variables === null) {
      return $result;
    }

    if ($variables->exists($as) === true) {
      $variables->remove($as);
    }
    $variables->add($as, $result);

    return $this->renderChildren();
  }
}
