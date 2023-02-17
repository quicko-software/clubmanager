<?php

namespace Quicko\Clubmanager\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use Quicko\Clubmanager\Domain\Repository\LocationRepository;

class LocationViewHelper extends AbstractViewHelper
{
    /**
     * locationRepository.
     *
     * @var \Quicko\Clubmanager\Domain\Repository\LocationRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $locationRepository;

    public function injectLocationRepository(LocationRepository $locationRepository)
    {
        $this->locationRepository = $locationRepository;
    }

    public function initializeArguments()
    {
        $this->registerArgument('uid', 'int', 'The Location uid', true);
        $this->registerArgument(
            'as',
            'string',
            'Template variable name to assign; if not specified the ViewHelper returns the variable instead.',
            true
        );         
    }

    /**
     * Returns the location by uid
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
            $location = $this->locationRepository->findByUidWithoutStorageRestrictions($uid);
            if($as ) {
                $this->templateVariableContainer->add($this->arguments['as'], $location);
            }
        }
        return $this->renderChildren();
    }
}
