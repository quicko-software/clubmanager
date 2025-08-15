<?php

namespace Quicko\Clubmanager\Controller;

use Psr\Http\Message\ResponseInterface;
use Quicko\Clubmanager\Domain\Model\Location;
use Quicko\Clubmanager\Domain\Repository\LocationRepository;
use TYPO3\CMS\Core\Pagination\ArrayPaginator;
use TYPO3\CMS\Core\Pagination\SimplePagination;

class LocationController extends BaseSettingsController
{
  public function __construct(protected LocationRepository $locationRepository)
  {
  }

  public function detailAction(?Location $location = null): ResponseInterface
  {
    $this->setDefaultSettingsIfRequired();
    $this->view->assign('location', $location);

    return $this->htmlResponse();
  }

  public function listAction(int $currentPage = 1): ResponseInterface
  {
    $this->setDefaultSettingsIfRequired();
    $locations = $this->locationRepository->findPublicActive(
      [
        'member.level' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING,
        'lastname' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING,
        'firstname' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING,
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
