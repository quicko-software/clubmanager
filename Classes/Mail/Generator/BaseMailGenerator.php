<?php
namespace Quicko\Clubmanager\Mail\Generator;

use TYPO3\CMS\Core\Mail\FluidEmail;
use TYPO3\CMS\Fluid\View\TemplatePaths;

use Quicko\Clubmanager\Mail\Generator\Arguments\BaseMailGeneratorArguments;
use Quicko\Clubmanager\Mail\Generator\SubpathableFluidEmail;
use Quicko\Clubmanager\Utils\TypoScriptUtils;

abstract class BaseMailGenerator {

  protected $useCachedRepository;

  public function __construct($useCachedRepository = false) {
    $this->useCachedRepository = $useCachedRepository;
  }

  abstract public function getLabel(BaseMailGeneratorArguments $args) : string;
  abstract public function generateFluidMail(BaseMailGeneratorArguments $args) : ?FluidEmail;

  public function getFirstname(BaseMailGeneratorArguments $args)  : string {
    return '';
  }
  public function getLastname(BaseMailGeneratorArguments $args) : string {
    return '';
  }
  public function getMailTo(BaseMailGeneratorArguments $args) : string {
    return '';
  }
  public function getIdent(BaseMailGeneratorArguments $args) : string {
    return '';
  }

  public function cleanUp() :void {   }

  protected function createFluidMail($configRefPid) {
    $config = TypoScriptUtils::getTypoScriptValueForPage("plugin.tx_clubmanager.settings.mailView", $configRefPid ?? 1);
    $fluidMail = new SubpathableFluidEmail(new TemplatePaths($config));
    // in TYPO3 V12, this is hardcoded to 'Default' - setting it
    // to 'Standard' makes the code compatible with V11 AND V12
    // -> /Resources/Private/Templates/Email/Standard
    // 2024-01-26, stephanw
    $fluidMail->setTemplateSubpath('Standard'); 
    return $fluidMail;
  }
}