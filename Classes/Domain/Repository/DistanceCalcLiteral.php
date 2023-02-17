<?php

namespace Quicko\Clubmanager\Domain\Repository;

class DistanceCalcLiteral
{
  ///
  /// Get a select literal calculating the distance of geo coords.
  /// The names of the geo coord columns are 'latitude' and 'longitude',
  /// the coord name parameters are ':lat' and ':lng' (for value binding).
  ///
  public static function getSql($tableName)
  {
    return <<<END_OF_STRING
      DEGREES(
        IFNULL(ACOS(
          (
            SIN(RADIANS(:lat)) 
            *
            SIN(RADIANS($tableName.latitude))
          ) 
          + 
          (
            COS(RADIANS(:lat))
            *
            COS(RADIANS($tableName.latitude))
            *
            COS(RADIANS((:lng - $tableName.longitude)))
          )
        )
        * 60 * 1.1515  * 1.609344,0)
      )
    END_OF_STRING;
  }
}
