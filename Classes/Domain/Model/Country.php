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

use SJBR\StaticInfoTables\Domain\Model\Country as SysCountry;

class Country extends SysCountry
{
    /**
     * Returns the shortNameLL.
     *
     * @return \string $shortNameLL
     */
    public function getShortNameLL()
    {
        $L = $GLOBALS['TSFE']->sys_language_uid;
        if ($L < 1) {
            return $this->getShortNameLocal();
        } else {
            return $this->getShortNameEn();
        }
    }
}
