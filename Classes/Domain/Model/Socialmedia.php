<?php

namespace Quicko\Clubmanager\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use Quicko\Clubmanager\Domain\Model\Location;

class Socialmedia extends AbstractEntity
{
    const TYPE_FACEBOOK = 0;
    const TYPE_INSTAGRAM = 1;
    const TYPE_YOUTUBE = 2;
    const TYPE_TWITTER = 3;

    /**
     * type.
     *
     * @var \integer
     */
    protected $type;

    /**
     * Url.
     *
     * @var \string
     */
    protected $url;

    /**
     * Location.
     *
     * @var Location
     */
    protected $location;
    

    /**
     * Returns the type.
     *
     * @return \integer $type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets the type.
     *
     * @param \integer $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Returns the url.
     *
     * @return \string $url
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Sets the url.
     *
     * @param \string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

        /**
     * Returns the location.
     *
     * @return Location $location
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Sets the location.
     *
     * @param Location $location
     */
    public function setLocation($location)
    {
        $this->location = $location;
    }
}
