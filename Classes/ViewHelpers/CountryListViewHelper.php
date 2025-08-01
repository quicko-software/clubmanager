<?php

namespace Quicko\Clubmanager\ViewHelpers;

use Quicko\Clubmanager\Domain\Repository\CountryRepository;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class CountryListViewHelper extends AbstractViewHelper
{
  /**
   * countryRepository.
   *
   * @var CountryRepository
   *
   * @TYPO3\CMS\Extbase\Annotation\Inject
   */
  protected $countryRepository;

  public function injectCountryRepository(CountryRepository $countryRepository): void
  {
    $this->countryRepository = $countryRepository;
  }

  /**
   * Returns the country by uid.
   */
  public function render(): mixed
  {
    return $this->countryRepository->findAll();
  }
}
