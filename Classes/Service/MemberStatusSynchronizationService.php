<?php

namespace Quicko\Clubmanager\Service;

use DateTime;
use Quicko\Clubmanager\Domain\Model\Member;
use Quicko\Clubmanager\Domain\Model\MemberStatusChange;
use Quicko\Clubmanager\Domain\Repository\MemberRepository;
use Quicko\Clubmanager\Domain\Repository\MemberStatusChangeRepository;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

class MemberStatusSynchronizationService
{
  public function __construct(
    protected MemberStatusChangeRepository $statusChangeRepository,
    protected MemberRepository $memberRepository,
    protected PersistenceManager $persistenceManager
  ) {
  }

  /**
   * Synchronisiert alle fälligen StatusChanges zu den Member-Feldern
   */
  public function synchronizePendingChanges(?DateTime $referenceDate = null): int
  {
    $refDate = $referenceDate ?? new DateTime('now');
    $pendingChanges = $this->statusChangeRepository->findPendingUntilDate($refDate);

    $processedCount = 0;
    $memberUpdates = []; // UID => neuester StatusChange

    // 1. Alle pending StatusChanges durchgehen und den neuesten pro Member ermitteln
    foreach ($pendingChanges as $statusChange) {
      $member = $statusChange->getMember();

      if (!$member instanceof Member) {
        // Member existiert nicht mehr -> StatusChange trotzdem als processed markieren
        $statusChange->setProcessed(true);
        $this->statusChangeRepository->update($statusChange);
        continue;
      }

      $memberUid = $member->getUid();

      // Prüfe ob dies der neueste StatusChange für diesen Member ist
      if (!isset($memberUpdates[$memberUid])) {
        $memberUpdates[$memberUid] = ['member' => $member, 'statusChange' => $statusChange];
      } else {
        $existingChange = $memberUpdates[$memberUid]['statusChange'];
        $existingDate = $existingChange->getEffectiveDate();
        $currentDate = $statusChange->getEffectiveDate();

        // Neueren nehmen (bei Gleichstand: höhere UID = zuletzt erstellt)
        if ($currentDate > $existingDate || ($currentDate == $existingDate && $statusChange->getUid() > $existingChange->getUid())) {
          $memberUpdates[$memberUid]['statusChange'] = $statusChange;
        }
      }

      // Alle als processed markieren
      $statusChange->setProcessed(true);
      $this->statusChangeRepository->update($statusChange);
      $processedCount++;
    }

    // 2. Für jeden Member den neuesten StatusChange anwenden
    foreach ($memberUpdates as $memberUid => $data) {
      /** @var Member $member */
      $member = $data['member'];
      /** @var MemberStatusChange $statusChange */
      $statusChange = $data['statusChange'];

      $this->applyStatusChangeToMember($member, $statusChange);
      $this->memberRepository->update($member);
    }

    if ($processedCount > 0) {
      $this->persistenceManager->persistAll();
    }

    return $processedCount;
  }

  /**
   * Wendet einen StatusChange auf die Member-Felder an
   */
  protected function applyStatusChangeToMember(Member $member, MemberStatusChange $statusChange): void
  {
    $newState = $statusChange->getState();
    $effectiveDate = $statusChange->getEffectiveDate();

    if ($effectiveDate === null) {
      throw new \InvalidArgumentException('StatusChange must have an effective date', 1717670002);
    }

    // State aktualisieren
    $member->setState($newState);

    // Zeitfelder je nach State setzen
    switch ($newState) {
      case Member::STATE_ACTIVE:
        if (!$member->getStarttime()) {
          $member->setStarttime($effectiveDate);
        }
        // endtime bleibt unverändert (könnte von vorherigem Cancel gesetzt sein)
        break;

      case Member::STATE_CANCELLED:
        // Bei Kündigung endtime setzen
        $member->setEndtime($effectiveDate);
        break;

      case Member::STATE_SUSPENDED:
        // Bei Ruhend: endtime setzen (kann später wieder reaktiviert werden)
        $member->setEndtime($effectiveDate);
        break;

      default:
        // STATE_UNSET, STATE_APPLIED: keine Zeitfelder setzen
        break;
    }
  }

  /**
   * Erstellt einen StatusChange-Record (z.B. für Billing-Integration)
   */
  public function createStatusChange(
    int $memberUid,
    int $state,
    DateTime $effectiveDate,
    string $note = '',
    int $createdBy = 0
  ): MemberStatusChange {
    $member = $this->memberRepository->findByIdentifier($memberUid);
    if (!$member) {
      throw new \InvalidArgumentException(sprintf('Member with UID %d not found.', $memberUid), 1717670001);
    }

    $statusChange = new MemberStatusChange();
    $statusChange->setMember($member);
    $statusChange->setState($state);
    $statusChange->setEffectiveDate($effectiveDate);
    $statusChange->setNote($note);
    $statusChange->setCreatedBy($createdBy);
    $statusChange->setProcessed(false);

    $this->statusChangeRepository->add($statusChange);
    $this->persistenceManager->persistAll();

    return $statusChange;
  }
}

