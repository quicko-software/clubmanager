<?php

namespace Quicko\Clubmanager\Controller;

use Psr\Http\Message\ResponseInterface;

use Quicko\Clubmanager\Controller\BaseSettingsController;
use Quicko\Clubmanager\Domain\Repository\LocationRepository;

class CitiesController extends BaseSettingsController
{
  protected $locationRepository;

  public function __construct(LocationRepository $locationRepository)
  {
    $this->locationRepository = $locationRepository;
  }

  public function listAction() : ResponseInterface
  {
    $this->setDefaultSettingsIfRequired();

    $cities = $this->locationRepository->findCities();
        
    $this->view->assign('cities', $cities);
    return $this->htmlResponse();
  }

  public function detailAction(string $city) : ResponseInterface
  {
    $this->setDefaultSettingsIfRequired();
    $locations = $this->locationRepository->findByCity($city);
    $this->view->assign('city', $city);
    $this->view->assign('locations', $locations);
    return $this->htmlResponse();
  }
 
}
