<?php

namespace Quicko\Clubmanager\Controller;

use Psr\Http\Message\ResponseInterface;

use TYPO3\CMS\Core\Pagination\ArrayPaginator;
use TYPO3\CMS\Core\Pagination\SimplePagination;

use Quicko\Clubmanager\Controller\BaseSettingsController;
use Quicko\Clubmanager\Domain\Repository\LocationRepository;
use Quicko\Clubmanager\Domain\Model\Location;

class LocationController extends BaseSettingsController
{
  protected $locationRepository;

  public function __construct(LocationRepository $locationRepository)
  {
    $this->locationRepository = $locationRepository;
  }

  public function detailAction(Location $location = null) : ResponseInterface
  {
    $this->setDefaultSettingsIfRequired();
    $this->view->assign('location', $location);
    return $this->htmlResponse();
  }

  /**
   * @param int $currentPage
   */
  public function listAction(int $currentPage = 1) : ResponseInterface
  {
    $this->setDefaultSettingsIfRequired();
    $locations = $this->locationRepository->findPublicActive(
      [
        'member.level' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING,
        'lastname' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING,
        'firstname' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING
      ]
    );

    $arrayPaginator = new ArrayPaginator($locations->toArray(), $currentPage, 64);
    $paging = new SimplePagination($arrayPaginator);
    $this->view->assignMultiple(
      [
        'locations' => $locations,
        'paginator' => $arrayPaginator,
        'paging' => $paging,
        'pages' => range(1, $paging->getLastPageNumber()),
      ]
    );
    return $this->htmlResponse();
  }
}
