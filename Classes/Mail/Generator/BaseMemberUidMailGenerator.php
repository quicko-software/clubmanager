<?php

namespace Quicko\Clubmanager\Mail\Generator;

use Quicko\Clubmanager\Records\CachedMemberRecordRepository;
use Quicko\Clubmanager\Records\MemberRecordRepository;
use Quicko\Mailjournal\Mail\Generator\Arguments\BaseMailGeneratorArguments;
use Quicko\Mailjournal\Mail\Generator\BaseMailGenerator;
use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class BaseMemberUidMailGenerator extends BaseMailGenerator
{
  /**
   * Summary of member.
   *
   * @var ?array<string,mixed>
   */
  private ?array $member = null;

  public function getMailTo(BaseMailGeneratorArguments $args): string
  {
    $member = $this->getMemberFromArgs($args);
    if (!$member) {
      return '?';
    }
    $address_email = $member['fe_users_email'];

    return $address_email ?? '';
  }

  public function getIdent(BaseMailGeneratorArguments $args): string
  {
    $member = $this->getMemberFromArgs($args);
    if (!$member) {
      return '?';
    }

    return $member['ident'] ?? '';
  }

  public function getFirstname(BaseMailGeneratorArguments $args): string
  {
    $member = $this->getMemberFromArgs($args);
    if (!$member) {
      return '?';
    }

    return $member['firstname'] ?? '';
  }

  public function getLastname(BaseMailGeneratorArguments $args): string
  {
    $member = $this->getMemberFromArgs($args);
    if (!$member) {
      return '?';
    }

    return $member['lastname'] ?? '';
  }

  /**
   * Summary of getMemberFromArgs.
   *
   * @return array<string,mixed>|null
   */
  protected function getMemberFromArgs(BaseMailGeneratorArguments $args): array|null
  {
    /** @var \Quicko\Clubmanager\Mail\Generator\Arguments\GenericMemberMailArguments $memberArgs */
    $memberArgs = $args;

    if ($this->member && $this->member['uid'] == $memberArgs->memberUid) {
      return $this->member;
    }
    $repo = $this->getMemberRepo();

    if (property_exists($memberArgs, 'memberUid') && $memberArgs->memberUid) {
      $m = $repo->findByUid($memberArgs->memberUid);
      if ($m) {
        $this->member = $m;

        return $m;
      }
    }

    return null;
  }

  protected function getMemberRepo(): MemberRecordRepository
  {
    /** @var MemberRecordRepository */
    $repo = null;
    if ($this->useCachedRepository) {
      /** @var MemberRecordRepository */
      $repo = GeneralUtility::makeInstance(CachedMemberRecordRepository::class);
    } else {
      /** @var MemberRecordRepository */
      $repo = GeneralUtility::makeInstance(MemberRecordRepository::class);
    }

    return $repo;
  }
}
