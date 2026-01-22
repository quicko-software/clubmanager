<?php

declare(strict_types=1);

namespace Quicko\Clubmanager\Hooks;

use Quicko\Clubmanager\Domain\Model\Member;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Hook to validate member data before saving.
 *
 * Validates that:
 * - A member cannot be set to STATE_ACTIVE without a valid ident (Mitgliedsnummer)
 *
 * IMPORTANT: This hook must be registered BEFORE other member hooks to block
 * invalid state transitions early.
 */
class MemberCheckHook
{
    private const TABLE_NAME = 'tx_clubmanager_domain_model_member';

    /**
     * Hook called before data is processed by DataHandler.
     * Prevents status change to ACTIVE if ident is missing.
     */
    public function processDatamap_preProcessFieldArray(
        array &$fieldArray,
        string $table,
        string|int $id,
        DataHandler $dataHandler
    ): void {
        if ($table !== self::TABLE_NAME) {
            return;
        }

        // Prüfe ob Status auf ACTIVE gesetzt wird
        $newState = $fieldArray['state'] ?? null;
        if ($newState === null || (int) $newState !== Member::STATE_ACTIVE) {
            return;
        }

        // Prüfe ident - aus fieldArray oder bestehendem Record
        $ident = $fieldArray['ident'] ?? null;
        if ($ident === null && is_numeric($id)) {
            $record = BackendUtility::getRecord(self::TABLE_NAME, (int) $id, 'ident');
            if (is_array($record)) {
                $ident = $record['ident'] ?? null;
            } else {
                $ident = null;
            }
        }

        if ($ident === null || trim((string) $ident) === '') {
            // Blockiere Status-Änderung und zeige Flash-Message
            unset($fieldArray['state']);

            $this->addFlashMessage(
                'Mitgliedsnummer (ident) fehlt – Aktivierung nicht möglich. Bitte zuerst eine Mitgliedsnummer vergeben.',
                'Validierungsfehler',
                ContextualFeedbackSeverity::ERROR
            );
        }
    }

    private function addFlashMessage(string $message, string $title, ContextualFeedbackSeverity $severity): void
    {
        $flashMessage = GeneralUtility::makeInstance(
            FlashMessage::class,
            $message,
            $title,
            $severity,
            true
        );

        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        $flashMessageService->getMessageQueueByIdentifier()->enqueue($flashMessage);
    }
}
