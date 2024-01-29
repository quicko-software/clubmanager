<?php

namespace Quicko\Clubmanager\Controller;

use Psr\Http\Message\ResponseInterface;
use Quicko\Clubmanager\Domain\Model\Location;
use Quicko\Clubmanager\Domain\Model\Member;
use Quicko\Clubmanager\Domain\Repository\MemberRepository;
use TYPO3\CMS\Core\Pagination\ArrayPaginator;
use TYPO3\CMS\Core\Pagination\SimplePagination;

class MemberController extends BaseSettingsController
{
  public function __construct(protected MemberRepository $memberRepository)
  {
    $this->memberRepository = $memberRepository;
  }

  public function detailAction(Member $member = null, Location $location = null): ResponseInterface
  {
    $this->setDefaultSettingsIfRequired();

    if ($member != null) {
      $this->view->assign('member', $member);
    } else if ($location != null) {
      $this->view->assign('member', $location->getMember());
    }

    return $this->htmlResponse();
  }

  public function listAction(int $currentPage = 1): ResponseInterface
  {
    $this->setDefaultSettingsIfRequired();
    $members = $this->memberRepository->findActivePublic(
      [
        'level' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING,
        'lastname' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING,
        'firstname' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING,
      ]
    );

    $arrayPaginator = new ArrayPaginator($members->toArray(), $currentPage, 64);
    $paging = new SimplePagination($arrayPaginator);

    $this->view->assignMultiple(
      [
        'members' => $members,
        'paginator' => $arrayPaginator,
        'paging' => $paging,
        'pages' => range(1, $paging->getLastPageNumber()),
      ]
    );

    return $this->htmlResponse();
  }
}
