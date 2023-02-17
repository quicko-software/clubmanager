<?php

namespace Quicko\Clubmanager\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use Quicko\Clubmanager\Domain\Repository\CountryRepository;

class CountryViewHelper extends AbstractViewHelper
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

    public function initializeArguments()
    {
        $this->registerArgument('uid', 'int', 'The country uid', true);
        $this->registerArgument(
            'as',
            'string',
            'Template variable name to assign; if not specified the ViewHelper returns the variable instead.',
            false
        );         
    }

    /**
     * Returns the country by uid
     *
     * @return string
     */
    public function render()
    {
        if ($this->templateVariableContainer->exists($this->arguments['as']) === TRUE) {
            $this->templateVariableContainer->remove($this->arguments['as']);
        }        
        $uid = $this->arguments['uid'];
        $as = array_key_exists("as",  $this->arguments) ? $this->arguments['as'] : null;
        if ($uid > 0) {
            $country = $this->countryRepository->findOneByUid($uid);
            if($as ) {
                $this->templateVariableContainer->add($this->arguments['as'], $country);
            } else {
                return $country->getNameLocalized();
            }
        }
        return $this->renderChildren();
    }
}
