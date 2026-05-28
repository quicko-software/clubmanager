<?php

namespace Quicko\Clubmanager\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

use Quicko\Clubmanager\Domain\Helper\States;

class StateListViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * Summary of renderStatic
     * @param array<string,mixed> $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return array<array<string,mixed>>
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ): array {
        return States::getStates();
    }
}
