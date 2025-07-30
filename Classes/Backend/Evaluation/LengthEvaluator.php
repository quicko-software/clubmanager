<?php

namespace Quicko\Clubmanager\Backend\Evaluation;

use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class for field value validation/evaluation to be used in 'eval' of TCA.
 */
class LengthEvaluator
{
  /**
   * JavaScript code for client side validation/evaluation.
   *
   * @return string JavaScript code for client side validation/evaluation
   */
  public function returnFieldJS(): string
  {
    return 'return value;';
  }

  /**
   * Server-side validation/evaluation on saving the record.
   *
   * @param string $value The field value to be evaluated
   * @param string $is_in The "is_in" value of the field configuration from TCA
   * @param bool   $set   Boolean defining if the value is written to the database or not. Must be passed by reference and changed if needed.
   *
   * @return string Evaluated field value
   */
  public function evaluateFieldValue($value, $is_in, &$set): string
  {
    if (strlen((string) $value) < 3) {
      $set = false;
      /** @var FlashMessage $message */
      $message = GeneralUtility::makeInstance(
        FlashMessage::class,
        'Der Wert im ident Feld entspricht nicht den Vorgaben (mindestens 3 Zeichen) - bitte tätigen Sie die Eingabe erneut.',
        'Error',
        ContextualFeedbackSeverity::ERROR
      );

      $this->dispatchMessage($message);
    }

    return $value;
  }

  private function dispatchMessage(FlashMessage $message): void
  {
    /** @var FlashMessageService $flashMessageService */
    $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
    $messageQueue = $flashMessageService->getMessageQueueByIdentifier();
    $messageQueue->addMessage($message);
  }
}
