<?php

namespace Quicko\Clubmanager\Domain\Model;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Extbase\Domain\Model\Category as SysCategory;

class Category extends SysCategory
{
    /**
     * title.
     *
     * @var \string
     */
    protected $title;

    /**
     * description.
     *
     * @var \string
     */
    protected $description;

    /**
     * slug.
     *
     * @var \string
     */
    protected $slug;    

    /**
     * Returns the title.
     *
     * @return \string $title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets the title.
     *
     * @param \string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Returns the description.
     *
     * @return \string $description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Sets the description.
     *
     * @param \string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Returns the slug.
     *
     * @return \string $slug
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Sets the slug.
     *
     * @param \string $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }  

}
