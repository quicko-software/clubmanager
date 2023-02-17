<?php

namespace Quicko\Clubmanager\Utils;

use TYPO3\CMS\Core\Http\JsonResponse;

use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

use Psr\Http\Message\ResponseInterface;


//
// For usage in backend controllers.
//
class JsonResponseUtil
{
  public static function guardedRun(callable $actionLikeFunction): ResponseInterface
  {
    try {
      return $actionLikeFunction();
    }
    catch (\Throwable $e) {
      return self::returnWithFlashMessage(
        null,
        500,
        'error',
        LocalizationUtility::translate(
          'LLL:EXT:clubmanager/Resources/Private/Language/locallang_be.xlf:bem.apiCall.flash.error.title',
          'clubmanager'
        ),
        $e->getMessage()
      );
    }
  }

  public static function returnWithFlashMessage(
    mixed $data,
    int $statusCode,
    ?string $messageType,
    ?string $messageTitle, 
    ?string $messageText 
  ): ResponseInterface
  {
    return new JsonResponse([
      'flash' => [
        'messageType' => $messageType,
        'messageTitle' => $messageTitle,
        'messageText' => $messageText,
      ],
      'data' => $data
    ], $statusCode);
  }

  public static function returnWithoutFlash(mixed $data, int $statusCode): ResponseInterface
  {
    return new JsonResponse([
      'data' => $data
    ], $statusCode);
  }
}
