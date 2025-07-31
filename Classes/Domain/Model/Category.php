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
   * slug.
   */
  protected string $slug;

  /**
   * Returns the slug.
   *
   * @return string $slug
   */
  public function getSlug(): string
  {
    return $this->slug;
  }

  /**
   * Sets the slug.
   */
  public function setSlug(string $slug): void
  {
    $this->slug = $slug;
  }
}
