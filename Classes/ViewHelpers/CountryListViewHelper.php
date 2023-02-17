<?php

namespace Quicko\Clubmanager\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use Quicko\Clubmanager\Domain\Repository\CountryRepository;

class CountryListViewHelper extends AbstractViewHelper
{
  /**
   * countryRepository.
   *
   * @var \Quicko\Clubmanager\Domain\Repository\CountryRepository
   * @TYPO3\CMS\Extbase\Annotation\Inject
   */
  protected $countryRepository;

  public function injectCountryRepository(CountryRepository $countryRepository)
  {
    $this->countryRepository = $countryRepository;
  }

  /**
   * Returns the country by uid
   *
   * @return string
   */
  public function render()
  {
      return $this->countryRepository->findAll();
  }
}
