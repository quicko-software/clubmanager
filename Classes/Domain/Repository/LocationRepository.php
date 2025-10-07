<?php

namespace Quicko\Clubmanager\Domain\Repository;

use Quicko\Clubmanager\Domain\Model\Location;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * @extends Repository<Location>
 */
class LocationRepository extends Repository
{
  use PersistAndRefetchTrait;

  /**
   * @return QueryResultInterface|object[]
   */
  public function findByUidWithHidden($uid): array|QueryResultInterface
  {
    $query = $this->createQuery();
    $querySettings = $query->getQuerySettings();
    $querySettings->setIgnoreEnableFields(true);

    return $query->matching(
      $query->logicalAnd(
        $query->equals('uid', $uid),
      )
    )->execute();
  }

  public function findByUidWithoutStorageRestrictions(int $uid): Location|null
  {
    $query = $this->createQuery();
    $querySettings = $query->getQuerySettings();
    $querySettings->setRespectStoragePage(false);

    return $query->matching(
      $query->logicalAnd(
        $query->equals('uid', $uid),
      )
    )->execute()->getFirst();
  }

  public function findByCity(string $cityName): array|QueryResultInterface
  {
    $query = $this->createQuery();

    return $query->matching(
      $query->logicalAnd(
        $query->equals('city', $cityName),

        $query->equals('member.state', \Quicko\Clubmanager\Domain\Model\Member::STATE_ACTIVE)
      )
    )
    ->setOrderings([
      'lastname' => QueryInterface::ORDER_ASCENDING]
    )
    ->execute();
  }

  public function findCities()
  {
    $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_clubmanager_domain_model_location');
    $storagePids = StoragePids::getList();
    $rows = $queryBuilder
      ->select('tx_clubmanager_domain_model_location.city as name')
      ->addSelectLiteral(
        $queryBuilder->expr()->count('tx_clubmanager_domain_model_location.uid', 'counter')
      )
      ->from('tx_clubmanager_domain_model_location')
      ->join(
        'tx_clubmanager_domain_model_location',
        'tx_clubmanager_domain_model_member',
        'member',
        'member.uid = tx_clubmanager_domain_model_location.member'
      )
      ->where(
        $queryBuilder->expr()->neq('tx_clubmanager_domain_model_location.city', "''"),
        $queryBuilder->expr()->eq(
          'member.state',
          \Quicko\Clubmanager\Domain\Model\Member::STATE_ACTIVE
        ),
        $queryBuilder->expr()->in('tx_clubmanager_domain_model_location.pid', $queryBuilder->createNamedParameter($storagePids, Connection::PARAM_INT_ARRAY))
      )
      ->groupBy('tx_clubmanager_domain_model_location.city')
      ->orderBy('name')

      ->executeQuery()->fetchAllAssociative();

    return $rows;
  }

  public function findCategories()
  {
    $table = 'tx_clubmanager_domain_model_location';
    $storagePids = StoragePids::getList();
    $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);

    $rows = $queryBuilder
      ->select('cat.uid')
      ->select('cat.title')
      ->addSelectLiteral(
        $queryBuilder->expr()->count($table . '.uid', 'counter')
      )
      ->from($table)
      ->join(
        $table,
        'tx_clubmanager_location_category_mm',
        'mm',
        $queryBuilder->expr()->eq('mm.uid_local', $queryBuilder->quoteIdentifier($table . '.uid'))
      )
      ->join(
        'mm',
        'sys_category',
        'cat',
        $queryBuilder->expr()->eq('cat.uid', $queryBuilder->quoteIdentifier('mm.uid_foreign'))
      )
      ->where(
        $queryBuilder->expr()->in('tx_clubmanager_domain_model_location.pid', $queryBuilder->createNamedParameter($storagePids, Connection::PARAM_INT_ARRAY))
      )
      ->groupBy('cat.uid', 'cat.title')
      ->orderBy('counter', 'DESC')
      ->executeQuery();

    return $rows;
  }

  public function findCountries()
  {
    $table = 'tx_clubmanager_domain_model_location';
    $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);

    $rows = $queryBuilder
      ->select('c.cn_short_local as name')
      ->addSelectLiteral(
        $queryBuilder->expr()->count($table . '.uid', 'counter')
      )
      ->from($table)
      ->join(
        $table,
        'static_countries',
        'c',
        $queryBuilder->expr()->eq('c.uid', $queryBuilder->quoteIdentifier($table . '.country'))
      )
      ->groupBy('c.cn_short_local')
      ->orderBy('counter', 'DESC')
      ->executeQuery();

    return $rows;
  }

  /**
   * Find all active member with a location that has a zip code
   * as given in the $zipList .
   *
   * @param array $zipList the list of allowed zips as string array, e.g. ['06120','06110','99712']
   */
  public function findWithZipCode($zipList)
  {
    if ($zipList === null || count($zipList) === 0) {
      return [];
    }

    $query = $this->createQuery();
    $query->matching(
      $query->logicalAnd(
        $query->in('zip', $zipList),
        $query->equals('member.state', \Quicko\Clubmanager\Domain\Model\Member::STATE_ACTIVE)
      )
    );

    return $query->execute()->toArray();
  }

  /**
   * Finds all members that have locations with a maximal distance
   * of $radiusKm to the given center $coords .
   *
   * @param array $coords   the coordinates as array, e.g. ['latitude' => 51.123, 'longitude' => 11.456 ]
   * @param int   $radiusKm the max distance of the member location to the given $coords
   */
  public function findAround($coords, $radiusKm): array|QueryResultInterface
  {
      $distanceMath = DistanceCalcLiteral::getSql('tx_clubmanager_domain_model_location');
      $table = 'tx_clubmanager_domain_model_location';
      $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
      $query = $queryBuilder
          ->addSelectLiteral('tx_clubmanager_domain_model_location.*')
          ->addSelectLiteral($distanceMath . ' AS distance')
          ->from($table)
          ->join(
              $table,
              'tx_clubmanager_domain_model_member',
              'tx_clubmanager_domain_model_member',
              $queryBuilder->expr()->eq('tx_clubmanager_domain_model_member.uid', 'tx_clubmanager_domain_model_location.uid')
          )
          ->where($distanceMath . ' < ' . $queryBuilder->createNamedParameter($radiusKm))
          ->andWhere(
              $queryBuilder->expr()->eq('tx_clubmanager_domain_model_location.hidden', 0),
              $queryBuilder->expr()->eq('tx_clubmanager_domain_model_location.deleted', 0),
              $queryBuilder->expr()->eq('tx_clubmanager_domain_model_member.hidden', 0),
              $queryBuilder->expr()->eq('tx_clubmanager_domain_model_member.deleted', 0),
              $queryBuilder->expr()->eq('tx_clubmanager_domain_model_member.state', \Quicko\Clubmanager\Domain\Model\Member::STATE_ACTIVE),
              $queryBuilder->expr()->in('tx_clubmanager_domain_model_location.pid', StoragePids::getList())
          )
          ->orderBy('distance', 'ASC')
          ->addOrderBy('tx_clubmanager_domain_model_location.lastname')
          ->setParameter('lat',  $coords['latitude'])
          ->setParameter('lng', $coords['longitude'])
          ->setParameter('radiusKm', $radiusKm)

      ;

    return $query->executeQuery();
  }

  public function findPublicActive(?array $sorting = null): QueryResultInterface
  {
    $query = $this->createQuery();
    $query->matching(
      $query->logicalAnd(
        $query->equals('member.state', \Quicko\Clubmanager\Domain\Model\Member::STATE_ACTIVE)
      )
    );
    if ($sorting != null) {
      $query->setOrderings($sorting);
    }
    $result = $query->execute();

    return $result;
  }

  protected function createQueryByMemberUidWithHidden($memberUid, $kind): QueryInterface
  {
    $query = $this->createQuery();
    $querySettings = $query->getQuerySettings();
    $querySettings->setIgnoreEnableFields(true);
    $query->matching(
      $query->logicalAnd(
        $query->equals('member', $memberUid),
        $query->equals('kind', $kind)
      )
    );

    return $query;
  }

  /**
   * @return ?Location
   */
  public function findMainLocByMemberUidWithHidden(int $memberUid): Location|null
  {
    $query = $this->createQueryByMemberUidWithHidden($memberUid, 0);

    return $query->execute()->getFirst();
  }

  /**
   * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface<Location>|object[]
   */
  public function findSubLocsByMemberUidWithHidden(int $memberUid): array|QueryResultInterface
  {
    $query = $this->createQueryByMemberUidWithHidden($memberUid, 1);

    return $query->execute();
  }
}
