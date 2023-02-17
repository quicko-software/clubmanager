<?php

namespace Quicko\Clubmanager\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Pagination\ArrayPaginator;
use TYPO3\CMS\Core\Pagination\SimplePagination;

use Quicko\Clubmanager\Controller\BaseSettingsController;
use Quicko\Clubmanager\Domain\Repository\MemberRepository;
use Quicko\Clubmanager\Domain\Model\Location;

class MemberController extends BaseSettingsController
{
  protected $memberRepository;

  public function __construct(MemberRepository $memberRepository)
  {
    $this->memberRepository = $memberRepository;
  }

  public function detailAction(Location $location = null) : ResponseInterface
  {
    $this->setDefaultSettingsIfRequired();
    if ($location) {
      $this->view->assign('member', $location->getMember());
    }
    return $this->htmlResponse();
  }

  /**
   * @param int $currentPage
   */
  public function listAction(int $currentPage = 1) : ResponseInterface
  {
    $this->setDefaultSettingsIfRequired();
    $members = $this->memberRepository->findActivePublic(
      [
        'level' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING,
        'lastname' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING,
        'firstname' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING
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
