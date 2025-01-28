<?php

namespace Quicko\Clubmanager\Mail\Generator;

use TYPO3\CMS\Core\Utility\GeneralUtility;

use Quicko\Clubmanager\Records\CachedMemberRecordRepository;
use Quicko\Clubmanager\Records\MemberRecordRepository;
use Quicko\Clubmanager\Mail\Generator\Arguments\BaseMailGeneratorArguments;
use Quicko\Clubmanager\Mail\Generator\Arguments\MemberUidArguments;

abstract class BaseMemberUidMailGenerator extends BaseMailGenerator
{
  /**
   * Summary of member
   * @var ?array<string,mixed>
   */
  private ?array $member = null;

  public function getMailTo(BaseMailGeneratorArguments $args): string
  {
    $member = $this->getMemberFromArgs($args);
    if (!$member) return "?";
    $address_email = $member['fe_users_email'];
    return $address_email ?? "";
  }

  public function getIdent(BaseMailGeneratorArguments $args): string
  {
    $member = $this->getMemberFromArgs($args);
    if (!$member) return "?";
    return $member['ident'] ?? "";
  }

  public function getFirstname(BaseMailGeneratorArguments $args): string
  {
    $member = $this->getMemberFromArgs($args);
    if (!$member) return "?";
    return $member['firstname'] ?? "";
  }
  
  public function getLastname(BaseMailGeneratorArguments $args): string
  {
    $member = $this->getMemberFromArgs($args);
    if (!$member) return "?";
    return $member['lastname'] ?? "";
  }


  /**
   * Summary of getMemberFromArgs
   * @param \Quicko\Clubmanager\Mail\Generator\Arguments\BaseMailGeneratorArguments $args
   * @return array<string,mixed>|null
   */
  protected function getMemberFromArgs(BaseMailGeneratorArguments $args): array|null
  {
    /** @var MemberUidArguments $memberArgs */
    $memberArgs = $args;

    if ($this->member && $this->member["uid"] == $memberArgs->memberUid) {
      return $this->member;
    }
    $repo = $this->getMemberRepo();
    $m = $repo->findByUid($memberArgs->memberUid);
    if($m) {
      $this->member = $m;
      return $m;
    }
    return null;
  }

  protected function getMemberRepo(): MemberRecordRepository
  {
    /** @var MemberRecordRepository */
    $repo = null;
    if($this->useCachedRepository) {
       /** @var MemberRecordRepository */
      $repo = GeneralUtility::makeInstance(CachedMemberRecordRepository::class);
    } else {
       /** @var MemberRecordRepository */
      $repo = GeneralUtility::makeInstance(MemberRecordRepository::class);
    }
    return $repo;
  }
}
