<?php

declare(strict_types=1);

use Quicko\Clubmanager\Utils\BackendModuleHelper;

return [
  'clubmanager' => [
    'labels' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_be.xlf:backend_main_module_name',
    'iconIdentifier' => 'tx-clubmanager_icon-be_mod_clubmanager',
    'navigationComponent' => '@typo3/backend/page-tree/page-tree-element',
  ],
  'memberlist' => 
    BackendModuleHelper::createAdvertisingModuleDescripterV12('memberlist','ClubmanagerProMemberList'),
  'settlements' => 
    BackendModuleHelper::createAdvertisingModuleDescripterV12('settlements','ClubmanagerBillingSettlements'),
  'events' => 
    BackendModuleHelper::createAdvertisingModuleDescripterV12('events','ClubmanagerCalendarEvents'),
  'membershipstatistics' => 
    BackendModuleHelper::createAdvertisingModuleDescripterV12('membershipstatistics','ClubmanagerStatisticsMembershipstatistics'),
  'mailtasks' =>
    BackendModuleHelper::createAdvertisingModuleDescripterV12('mailtasks','ClubmanagerProMailtasks'),
];
