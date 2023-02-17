<?php

namespace Quicko\Clubmanager\Utils;

use Quicko\Clubmanager\Domain\Model\Location;

class LocationSlugUtil
{
  // basic idea from https://stackoverflow.com/questions/63304723/typo3-extbase-how-to-create-unique-slug-within-create-action

  public static function generateLocationSlug(Location $location, $pid)
  {
    return SlugUtil::generateSlug(
      [
        'firstname' => $location->getFirstname(),
        'lastname' => $location->getLastname(),
        'company' => $location->getCompany(),
        'city' => $location->getCity(),
      ], $pid,'tx_clubmanager_domain_model_location');
  }
}
