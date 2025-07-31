<?php

namespace Quicko\Clubmanager\Evaluation;

use InvalidArgumentException;
use Quicko\Clubmanager\Utils\BankAccountDataHelper;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class IbanEvaluation
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
   * @param string $isIn  The "is_in" value of the field configuration from TCA
   * @param bool   $set   boolean defining if the value is written to the database or not
   *
   * @return string Evaluated field value
   */
  public function evaluateFieldValue($value, $isIn, &$set): string
  {
    try {
      BankAccountDataHelper::sanitizeIban($value);
    } catch (InvalidArgumentException $e) {
      $this->flashMessage(LocalizationUtility::translate('tca.invalid_field', 'Clubmanager') ?? '', $e->getMessage(), ContextualFeedbackSeverity::ERROR);
      $set = false;
    }

    return $value;
  }

  /**
   * Server-side validation/evaluation on opening the record.
   *
   * @param array $parameters Array with key 'value' containing the field value from the database
   *
   * @return string Evaluated field value
   */
  public function deevaluateFieldValue(array $parameters): string
  {
    return $parameters['value'];
  }

  protected function flashMessage(string $messageTitle, string $messageText, ContextualFeedbackSeverity $severity = ContextualFeedbackSeverity::ERROR): void
  {
    $message = GeneralUtility::makeInstance(
      FlashMessage::class,
      $messageText,
      $messageTitle,
      $severity,
      true
    );

    $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
    $messageQueue = $flashMessageService->getMessageQueueByIdentifier();
    $messageQueue->addMessage($message);
  }
}
