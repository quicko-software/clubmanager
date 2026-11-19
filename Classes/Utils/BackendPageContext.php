<?php

declare(strict_types=1);

namespace Quicko\Clubmanager\Utils;

use Quicko\Clubmanager\Domain\Helper\QueryGenerator;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Error\Http\ForbiddenException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Seitenkontext der Clubmanager-Backendmodule: Die im Seitenbaum gewählte Seite
 * bestimmt Mandant und Ordner. Ohne Auswahl wird kein Ordner geraten.
 */
final class BackendPageContext
{
  /**
   * Liefert die im Seitenbaum gewählte Seite, sofern der Redakteur sie lesen darf, sonst 0.
   */
  public static function selectedPid(mixed $id): int
  {
    $pid = (int) $id;
    if ($pid <= 0) {
      return 0;
    }
    $backendUser = self::backendUser();
    if ($backendUser === null || BackendUtility::readPageAccess($pid, $backendUser->getPagePermsClause(1)) === false) {
      return 0;
    }

    return $pid;
  }

  /**
   * Sichert Datenrouten ab: gewählte Seite vorhanden, Seitenrecht und Modulrecht vorhanden.
   *
   * @throws ForbiddenException
   */
  public static function requireAccessiblePid(mixed $pid, string $moduleIdentifier): int
  {
    $pid = (int) $pid;
    if ($pid <= 0) {
      throw new ForbiddenException('Clubmanager module page is missing.', 1795101001);
    }
    $backendUser = self::backendUser();
    if ($backendUser === null || !$backendUser->check('modules', $moduleIdentifier)) {
      throw new ForbiddenException('No access to the Clubmanager module.', 1795101002);
    }
    if (BackendUtility::readPageAccess($pid, $backendUser->getPagePermsClause(1)) === false) {
      throw new ForbiddenException('No access to the selected Clubmanager page.', 1795101003);
    }

    return $pid;
  }

  /**
   * Gewählte Seite samt aller Unterseiten, die der Redakteur lesen darf.
   * Statistiken und Listen werten damit genau den gewählten Mandanten- bzw. Ordnerzweig aus.
   *
   * @return list<int>
   */
  public static function treePids(int $pid): array
  {
    $backendUser = self::backendUser();
    $permsClause = $backendUser !== null ? $backendUser->getPagePermsClause(1) : '';
    $queryGenerator = GeneralUtility::makeInstance(QueryGenerator::class);

    return array_values(array_map('intval', $queryGenerator->getTreeList($pid, 99, 0, $permsClause)));
  }

  /**
   * Blendet über das BEModule-Layout den Hinweis zur Ordnerwahl statt der Liste ein.
   */
  public static function addNoPageHint(ModuleTemplate $moduleTemplate): void
  {
    $moduleTemplate->assign('noPageSelected', true);
  }

  private static function backendUser(): ?BackendUserAuthentication
  {
    $backendUser = $GLOBALS['BE_USER'] ?? null;

    return $backendUser instanceof BackendUserAuthentication ? $backendUser : null;
  }
}
