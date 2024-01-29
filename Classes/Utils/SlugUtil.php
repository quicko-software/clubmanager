<?php

namespace Quicko\Clubmanager\Utils;

use Doctrine\DBAL\Result;
use Normalizer;
use TYPO3\CMS\Core\Charset\CharsetConverter;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\Model\RecordStateFactory;
use TYPO3\CMS\Core\DataHandling\SlugHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SlugUtil
{
  public static function sanitizeParameter(string $slug): string
  {
    // Convert to lowercase + remove tags
    $slug = mb_strtolower($slug, 'utf-8');
    $slug = str_replace('/', '-', $slug);
    $slug = strip_tags($slug);

    $fallbackCharacter = '-';
    $slug = preg_replace('/[ \t\x{00A0}\-+_]+/u', $fallbackCharacter, $slug);

    if (!Normalizer::isNormalized((string) $slug)) {
      $slug = Normalizer::normalize((string) $slug);
    }

    // Convert extended letters to ascii equivalents
    // The specCharsToASCII() converts "€" to "EUR"
    /** @var CharsetConverter $charsetConverter */
    $charsetConverter = GeneralUtility::makeInstance(CharsetConverter::class);
    $slug = $charsetConverter->specCharsToASCII('utf-8', $slug);

    // Get rid of all invalid characters, but allow slashes
    $slug = preg_replace('/[^\p{L}\p{M}0-9\/' . preg_quote($fallbackCharacter) . ']/u', '', $slug);

    // Convert multiple fallback characters to a single one
    if ($fallbackCharacter !== '') {
      $slug = preg_replace('/' . preg_quote($fallbackCharacter) . '{2,}/', $fallbackCharacter, $slug);
    }

    // Ensure slug is lower cased after all replacement was done
    $slug = mb_strtolower($slug, 'utf-8');

    return $slug;
  }

  public static function generateSlug(array $record, int $pid, string $TABLE_NAME): string
  {
    $SLUG_FIELD_NAME = 'slug';
    // $record = ['label' => $label]; // label is the field as configured in tca

    /** @var SlugHelper $slugHelper */
    $slugHelper = GeneralUtility::makeInstance(
      SlugHelper::class,
      $TABLE_NAME,
      $SLUG_FIELD_NAME,
      $GLOBALS['TCA'][$TABLE_NAME]['columns'][$SLUG_FIELD_NAME]['config']
    );

    $slug = $slugHelper->generate($record, $pid);

    return $slug;
  }

  /**
   * basic idea from https://stackoverflow.com/questions/63304723/typo3-extbase-how-to-create-unique-slug-within-create-action.
   *
   * @param int    $uid           UID of record saved in DB
   * @param string $tableName     Name of the table to lookup for uniques
   * @param string $slugFieldName Name of the slug field
   *
   * @return ?string Resolved unique slug
   *
   * @throws \TYPO3\CMS\Core\Exception\SiteNotFoundException
   */
  public static function generateUniqueSlug(int $uid, string $tableName, string $slugFieldName): ?string
  {
    /** @var ConnectionPool $connectionPool */
    $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
    /** @var Connection $connection */
    $connection = $connectionPool->getConnectionForTable($tableName);
    $queryBuilder = $connection->createQueryBuilder();

    /** @var Result $result */
    $result = $queryBuilder
        ->select('*')
        ->from($tableName)
        ->where('uid=:uid')
        ->setParameter(':uid', $uid)
        ->execute();

    $record = $result->fetchAssociative();
    if (!$record) {
      return null;
    }

    //      Get field configuration
    $fieldConfig = $GLOBALS['TCA'][$tableName]['columns'][$slugFieldName]['config'];
    $evalInfo = GeneralUtility::trimExplode(',', $fieldConfig['eval'], true);

    //      Initialize Slug helper
    /** @var SlugHelper $slugHelper */
    $slugHelper = GeneralUtility::makeInstance(
      SlugHelper::class,
      $tableName,
      $slugFieldName,
      $fieldConfig
    );

    //      Generate slug
    $slug = $slugHelper->generate($record, $record['pid']);
    $state = RecordStateFactory::forName($tableName)
        ->fromArray($record, $record['pid'], $record['uid']);

    //      Build slug depending on eval configuration
    if (in_array('uniqueInSite', $evalInfo)) {
      $slug = $slugHelper->buildSlugForUniqueInSite($slug, $state);
    } elseif (in_array('uniqueInPid', $evalInfo)) {
      $slug = $slugHelper->buildSlugForUniqueInPid($slug, $state);
    } elseif (in_array('unique', $evalInfo)) {
      $slug = $slugHelper->buildSlugForUniqueInTable($slug, $state);
    }

    return $slug;
  }
}
