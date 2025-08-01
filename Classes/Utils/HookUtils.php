<?php

namespace Quicko\Clubmanager\Utils;

use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Log\Logger;


class HookUtils
{
  public static function getRecordUid(string $uidCandidate, DataHandler &$dataHandler): ?string
  {
    if (str_starts_with($uidCandidate, 'NEW')) {
      return array_key_exists($uidCandidate, $dataHandler->substNEWwithIDs)
        ? $dataHandler->substNEWwithIDs[$uidCandidate]
        : null
      ;
    }
    $trimmedUidCandidate = trim($uidCandidate);
    return empty($trimmedUidCandidate)
      ? null
      : $trimmedUidCandidate
    ;
  }

  public static function logError(Logger $logger, string $tableName, string $uid): void {
    $logger->error(
      sprintf('Failed to process datamap for command (table=%s, uid=%s)',
        $tableName,
        $uid
      )
    );
  }
}
