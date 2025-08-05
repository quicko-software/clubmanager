<?php

namespace Quicko\Clubmanager\Mail\Generator\Arguments;

use Quicko\Clubmanager\Mail\Generator\Arguments\BaseMailGeneratorArguments;

class MailGeneratorArgumentsSerializer
{

  public static function deserialize(string $json): BaseMailGeneratorArguments
  {
    $data = json_decode($json,true);
    $result = new BaseMailGeneratorArguments();
    if ($data) {
      foreach ($data as $key => $value) {
        try {
          $result->{$key} = $value;
        } catch (\Exception $e) {
          // TODO error handling
        }
      }
    } else {
      // TODO error handling
    }
    return $result;
  }

  public static function serialize(BaseMailGeneratorArguments $baseMailGeneratorArguments): string
  {
    $result = json_encode($baseMailGeneratorArguments);
    return $result ? $result : '';
  }
}
