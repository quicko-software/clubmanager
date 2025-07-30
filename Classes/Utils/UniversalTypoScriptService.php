<?php

namespace Quicko\Clubmanager\Utils;

use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Cache\Frontend\PhpFrontend;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\VisibilityAspect;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\TypoScript\IncludeTree\SysTemplateRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;
use TYPO3\CMS\Frontend\Page\PageInformation;

/**
 * Summary of UniversalTypoScriptService
 * https://stackoverflow.com/questions/77151557/typo3-templateservice-deprecation-how-to-get-plugin-typoscript-not-in-fe-cont.
 */
class UniversalTypoScriptService
{
  /**
   * Summary of __construct.
   *
   * @param PhpFrontend $typoScriptCache
   *
   * @phpstan-ignore-next-line
   */
  public function __construct(
    #[Autowire('@TYPO3\\CMS\\Core\\TypoScript\\FrontendTypoScriptFactory')]
    private $frontendTypoScriptFactory,
    #[Autowire(service: 'cache.typoscript')]
    private PhpFrontend $typoScriptCache,
    private SysTemplateRepository $sysTemplateRepository,
  ) {
  }

  public function getTypoScript(int $pageUid): mixed
  {
    // make sure, we don't get config from disabled TS templates in BE context
    $context = GeneralUtility::makeInstance(Context::class);
    $visibilityAspect = GeneralUtility::makeInstance(VisibilityAspect::class);
    $context->setAspect('visibility', $visibilityAspect);

    $settings = [];
    try {
      $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($pageUid);
    } catch (SiteNotFoundException $e) {
      $site = null;
    }

    if ($site) {
      $pageInformation = $this->getPageInformation($site, $pageUid);

      $conditionMatcherVariables = $this->prepareConditionMatcherVariables($site, $pageInformation);

      // first step of TypoScript calculations:
      // *always* called, even in FE fully cached pages context since the page
      // cache entry depends on setup condition verdicts, which depends on settings
      $frontendTypoScript = $this->frontendTypoScriptFactory->createSettingsAndSetupConditions(
        $site,
        $pageInformation->getSysTemplateRows(),
        $conditionMatcherVariables,
        $this->typoScriptCache,
      );

      // now get the actual full TS

      $ts = $this->frontendTypoScriptFactory->createSetupConfigOrFullSetup(
        true,  // $needsFullSetup -> USER_INT
        $frontendTypoScript,
        $site,
        $pageInformation->getSysTemplateRows(),
        $conditionMatcherVariables,
        '0',  // $type -> typeNum (default: 0; GET/POST param: type)
        $this->typoScriptCache,
        null,  // $request
      );

      if ($ts->hasPage() && $ts->hasSetup()) {
        $settings = $ts->getSetupTree()->toArray();
      }
    }

    return $settings;
  }

  protected function getPageInformation(Site $site, int $pageUid): PageInformation
  {
    $pageInformation = new PageInformation();
    $pageInformation->setId($pageUid);

    $pageRecord = BackendUtility::getRecord('pages', $pageUid, '*');
    $pageInformation->setPageRecord($pageRecord);

    $rootLine = [];
    if ($pageUid > 0) {
      try {
        $rootLine = GeneralUtility::makeInstance(RootlineUtility::class, $pageUid)->get();
      } catch (RuntimeException $e) {
        $rootLine = [];
      }
    }
    /* TYPO12 vs TYPO13 type check */
    if ($site instanceof Site && method_exists($site, 'getTypoScript') && $site->getTypoScript() !== null) {
      $rootLineUntilSite = [];
      foreach ($rootLine as $index => $rootlinePage) {
        $rootLineUntilSite[$index] = $rootlinePage;
        $pageId = (int) ($rootlinePage['uid'] ?? 0);
        if ($pageId === $site->getRootPageId()) {
          break;
        }
      }
      $rootLine = $rootLineUntilSite;
    }

    $pageInformation->setRootline($rootLine);
    $sysTemplateRows = $this->sysTemplateRepository->getSysTemplateRowsByRootline($rootLine);
    $pageInformation->setSysTemplateRows($sysTemplateRows);
    $localRootLine = $this->getLocalRootLine($site, $pageInformation);
    $pageInformation->setLocalRootline($localRootLine);

    return $pageInformation;
  }

  /**
   * Calculate "local" rootLine that stops at first root=1 template.
   */
  protected function getLocalRootLine(Site $site, PageInformation $pageInformation): array
  {
    $sysTemplateRows = $pageInformation->getSysTemplateRows();
    $rootLine = $pageInformation->getRootLine();
    $sysTemplateRowsIndexedByPid = array_combine(array_column($sysTemplateRows, 'pid'), $sysTemplateRows);
    $localRootline = [];
    foreach ($rootLine as $rootlinePage) {
      array_unshift($localRootline, $rootlinePage);
      $pageId = (int) ($rootlinePage['uid'] ?? 0);
      /* TYPO12 vs TYPO13 type check */
      if ($pageId === $site->getRootPageId() && method_exists($site, 'isTypoScriptRoot') && $site->isTypoScriptRoot()) {
        break;
      }
      if ($pageId > 0 && (int) ($sysTemplateRowsIndexedByPid[$pageId]['root'] ?? 0) === 1) {
        break;
      }
    }

    return $localRootline;
  }

  /**
   * Data available in TypoScript "condition" matching.
   */
  protected function prepareConditionMatcherVariables(Site $site, PageInformation $pageInformation): array
  {
    $topDownRootLine = $pageInformation->getRootLine();
    $localRootline = $pageInformation->getLocalRootLine();
    ksort($topDownRootLine);
    $language = $site->getDefaultLanguage();

    return [
        'pageId' => $pageInformation->getId(),
        'page' => $pageInformation->getPageRecord(),
        'fullRootLine' => $topDownRootLine,
        'localRootLine' => $localRootline,
        'site' => $site,
        'siteLanguage' => $language,
    ];
  }
}
