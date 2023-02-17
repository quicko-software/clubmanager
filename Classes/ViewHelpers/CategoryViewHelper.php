<?php

namespace Quicko\Clubmanager\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use Quicko\Clubmanager\Domain\Repository\CategoryRepository;

class CategoryViewHelper extends AbstractViewHelper
{
    /**
     * categoryRepository.
     *
     * @var \Quicko\Clubmanager\Domain\Repository\CategoryRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $categoryRepository;

    public function injectCategoryRepository(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function initializeArguments()
    {
        $this->registerArgument('uid', 'int', 'The category uid', true);
        $this->registerArgument(
            'as',
            'string',
            'Template variable name to assign; if not specified the ViewHelper returns the variable instead.',
            false
        );       
    }

    /**
     * Returns the category by uid
     *
     * @return string
     */
    public function render()
    {
        if ($this->templateVariableContainer->exists($this->arguments['as']) === TRUE) {
            $this->templateVariableContainer->remove($this->arguments['as']);
        }
        $as = $this->arguments['as'];
        $uid = $this->arguments['uid'];
        if ($uid > 0) {
            $category = $this->categoryRepository->findOneByUid($uid);
            if($as ) {
                $this->templateVariableContainer->add($as , $category);
            } 
            else {
                return $category->getTitle();
            }
        }
        return $this->renderChildren();
    }
}
