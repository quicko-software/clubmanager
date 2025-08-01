<?php

/*
 *   Copyright(c) 2022 codemacher UG (haftungsbeschränkt) All Rights Reserved.
 *
 *   Created on : 27.09.2022, 23:20:31
 */

namespace Quicko\Clubmanager\Controller\Backend;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class AdvertisingController extends ActionController
{
  public function __construct(protected ModuleTemplateFactory $moduleTemplateFactory)
  {
  }

  public function memberlistAction(): ResponseInterface
  {
    $moduleTemplate = $this->moduleTemplateFactory->create($this->request);

    return $moduleTemplate->renderResponse('Backend/Advertising/Memberlist');
  }

  public function mailtasksAction(): ResponseInterface
  {
    $moduleTemplate = $this->moduleTemplateFactory->create($this->request);

    return $moduleTemplate->renderResponse('Backend/Advertising/Mailtasks');
  }

  public function settlementsAction(): ResponseInterface
  {
    $moduleTemplate = $this->moduleTemplateFactory->create($this->request);

    return $moduleTemplate->renderResponse('Backend/Advertising/Settlements');
  }

  public function eventsAction(): ResponseInterface
  {
    $moduleTemplate = $this->moduleTemplateFactory->create($this->request);

    return $moduleTemplate->renderResponse('Backend/Advertising/Events');
  }

  public function membershipstatisticsAction(): ResponseInterface
  {
    $moduleTemplate = $this->moduleTemplateFactory->create($this->request);

    return $moduleTemplate->renderResponse('Backend/Advertising/Membershipstatistics');
  }
}
