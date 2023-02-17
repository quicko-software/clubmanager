<?php
namespace Quicko\Clubmanager\Mail\Generator;

use TYPO3\CMS\Core\Mail\FluidEmail;

use Quicko\Clubmanager\Mail\Generator\Arguments\BaseMailGeneratorArguments;
use Quicko\Clubmanager\Utils\TypoScriptUtils;
use TYPO3\CMS\Fluid\View\TemplatePaths;

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
    return new FluidEmail(new TemplatePaths($config));    
  }
}