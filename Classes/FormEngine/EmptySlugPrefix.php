<?php
declare(strict_types = 1);

namespace Quicko\Clubmanager\FormEngine;

use TYPO3\CMS\Backend\Form\FormDataProvider\TcaSlug;

class EmptySlugPrefix
{
    public function getPrefix(array $parameters, TcaSlug $reference): string
    {
        return '';
    }
}