<?php

namespace Quicko\Clubmanager\ViewHelpers;

use Quicko\Clubmanager\Domain\Helper\States;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class StateListViewHelper extends AbstractViewHelper
{
  /**
   * @return array<array<string, mixed>>
   */
  public function render(): array
  {
    return States::getStates();
  }
}
