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

}
