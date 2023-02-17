<?php
declare(strict_types = 1);

return [
  \Quicko\Clubmanager\Domain\Model\Category::class => [
    'tableName' => 'sys_category',
  ],  
  \Quicko\Clubmanager\Domain\Model\Country::class => [
    'tableName' => 'static_countries',
    'recordType' => \Quicko\Clubmanager\Domain\Model\Country::class
  ],
  \Quicko\Clubmanager\Domain\Model\FrontendUser::class => [
    'tableName' => 'fe_users',
    'properties' => [
      'hidden' => [
        'fieldName' => 'disable',
      ],
    ],
  ],
  \Quicko\Clubmanager\Domain\Model\FrontendUserGroup::class => [
    'tableName' => 'fe_groups',
  ],  
  \Quicko\Clubmanager\Domain\Model\ExtFileRef::class => [
    'tableName' => 'sys_file_reference',
    'properties' => [
      'originalFileIdentifier' => [
        'fieldName' => 'uid_local',
      ],
    ],
  ],
];
