<?php

namespace Quicko\Clubmanager\Mail\Generator;

use TYPO3\CMS\Core\Mail\FluidEmail;
use TYPO3\CMS\Fluid\View\TemplatePaths;

class SubpathableFluidEmail extends FluidEmail {
  public function __construct(TemplatePaths $templatePaths) {
    parent::__construct($templatePaths);
  }

  public function setTemplateSubpath(string $templateSubpath) : void {
    $this->view->getRenderingContext()->setControllerName($templateSubpath);
  }
}
