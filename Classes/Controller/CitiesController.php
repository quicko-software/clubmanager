<?php

namespace Quicko\Clubmanager\Controller;

use Psr\Http\Message\ResponseInterface;
use Quicko\Clubmanager\Domain\Repository\LocationRepository;

class CitiesController extends BaseSettingsController
{
  public function __construct(protected LocationRepository $locationRepository)
  {
  }

  public function listAction(): ResponseInterface
  {
    $this->setDefaultSettingsIfRequired();
    $cities = $this->locationRepository->findCities();

    $this->view->assign('cities', $cities);

    return $this->htmlResponse();
  }

  public function detailAction(string $city): ResponseInterface
  {
    $this->setDefaultSettingsIfRequired();
    $locations = $this->locationRepository->findByCity($city);
    $this->view->assign('city', $city);
    $this->view->assign('locations', $locations);

    return $this->htmlResponse();
  }
}
