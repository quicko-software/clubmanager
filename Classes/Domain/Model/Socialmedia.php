<?php

namespace Quicko\Clubmanager\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Socialmedia extends AbstractEntity
{
  public const TYPE_FACEBOOK = 0;
  public const TYPE_INSTAGRAM = 1;
  public const TYPE_YOUTUBE = 2;
  public const TYPE_TWITTER = 3;

  protected int $type;

  protected string $url;

  protected Location $location;

  public function getType(): int
  {
    return $this->type;
  }

  public function setType(int $type): void
  {
    $this->type = $type;
  }

  public function getUrl(): string
  {
    return $this->url;
  }

  public function setUrl(string $url): void
  {
    $this->url = $url;
  }

  public function getLocation(): Location
  {
    return $this->location;
  }

  public function setLocation(Location $location): void
  {
    $this->location = $location;
  }
}
