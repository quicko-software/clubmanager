<?php
namespace Quicko\Clubmanager\Mail\Generator;

use TYPO3\CMS\Core\Mail\FluidEmail;
use TYPO3\CMS\Fluid\View\TemplatePaths;

use Quicko\Clubmanager\Mail\Generator\Arguments\BaseMailGeneratorArguments;
use Quicko\Clubmanager\Mail\Generator\SubpathableFluidEmail;
use Quicko\Clubmanager\Utils\TypoScriptUtils;

abstract class BaseMailGenerator {

  protected bool $useCachedRepository;

  public function __construct(bool $useCachedRepository = false) {
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

  protected function createFluidMail(int $configRefPid): SubpathableFluidEmail {
    $config = TypoScriptUtils::getTypoScriptValueForPage("plugin.tx_clubmanager.settings.mailView", $configRefPid);
    $fluidMail = new SubpathableFluidEmail(self::buildTemplatePaths($config));
    // in TYPO3 V12, this is hardcoded to 'Default' - setting it
    // to 'Standard' makes the code compatible with V11 AND V12
    // -> /Resources/Private/Templates/Email/Standard
    // 2024-01-26, stephanw
    $fluidMail->setTemplateSubpath('Standard'); 
    return $fluidMail;
  }

  /**
   * Build TemplatePaths from a TypoScript "mailView" array.
   *
   * Fluid 4 (TYPO3 v14) dropped the TemplatePaths constructor, so
   * `new TemplatePaths($config)` silently discards the paths: every mail
   * then fails to render with "No paths configured" and stays in the
   * queue. The setters exist in v13 and v14 alike.
   *
   * @param mixed $config
   */
  protected static function buildTemplatePaths($config): TemplatePaths {
    $config = is_array($config) ? $config : [];
    $paths = new TemplatePaths();
    $paths->setTemplateRootPaths($config['templateRootPaths'] ?? []);
    $paths->setLayoutRootPaths($config['layoutRootPaths'] ?? []);
    $paths->setPartialRootPaths($config['partialRootPaths'] ?? []);

    return $paths;
  }
}