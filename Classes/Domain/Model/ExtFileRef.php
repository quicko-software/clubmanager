<?php

namespace Quicko\Clubmanager\Domain\Model;

// /
// / Class to fill a gap in Extbase - enables to create a file reference
// / by providing a typo3-core-file (result from upload).
// /
// / Requires configuration (Classes.php or typoscript) to map this class
// / to sys_file_reference.
// /
class ExtFileRef extends \TYPO3\CMS\Extbase\Domain\Model\FileReference
{
  /**
   * uid of a sys_file.
   *
   * @var int
   */
  protected $originalFileIdentifier;

  /**
   * Called by framework, just overwrite to update the own int-id of the file.
   *
   * @param \TYPO3\CMS\Core\Resource\FileReference $originalResource
   *
   * @return void
   */
  public function setOriginalResource(\TYPO3\CMS\Core\Resource\ResourceInterface $originalResource): void
  {
    $this->originalResource = $originalResource;
    $this->originalFileIdentifier = (int) $originalResource->getOriginalFile()->getUid();
  }

  /**
   * This is the magic function - give it a sys file.
   *
   * @return void
   */
  public function setFile(\TYPO3\CMS\Core\Resource\File $falFile): void
  {
    $this->originalFileIdentifier = $falFile->getUid();
  }

  public function getOriginalFileIdentifier(): int
  {
    return $this->originalFileIdentifier;
  }
}
