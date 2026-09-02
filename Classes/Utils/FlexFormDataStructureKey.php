<?php

declare(strict_types=1);

namespace Quicko\Clubmanager\Utils;

final class FlexFormDataStructureKey
{
  public static function matches(string $dataStructureKey, string $cType): bool
  {
    return in_array($dataStructureKey, [
      $cType,
      '*,' . $cType,
      $cType . ',list',
      $cType . ',' . $cType,
    ], true);
  }
}
