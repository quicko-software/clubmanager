<?php

/*
 *   Copyright(c) 2022 codemacher UG (haftungsbeschränkt) All Rights Reserved.
 *
 *   Created on : 27.09.2022, 23:20:31
 */

namespace Quicko\Clubmanager\Controller\Backend;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class AdvertisingController extends ActionController
{
  public function __construct(protected ModuleTemplateFactory $moduleTemplateFactory)
  {
  }

  protected function defaultAction(): ResponseInterface
  {
    /** @var ModuleTemplate $moduleTemplate */
    $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
    $moduleTemplate->setContent($this->view->render());

    return $this->htmlResponse($moduleTemplate->renderContent());
  }

  public function memberlistAction(): ResponseInterface
  {
    return $this->defaultAction();
  }

  public function mailtasksAction(): ResponseInterface
  {
    return $this->defaultAction();
  }

  public function settlementsAction(): ResponseInterface
  {
    return $this->defaultAction();
  }

  public function eventsAction(): ResponseInterface
  {
    return $this->defaultAction();
  }

  public function membershipstatisticsAction(): ResponseInterface
  {
    return $this->defaultAction();
  }
}
