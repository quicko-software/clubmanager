<?php

defined('TYPO3') or die();


return [
  'ctrl'      => [
    'title'                    => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_mail_task',
    'label'                    => 'send_state',
    'label_userFunc'           => \Quicko\Clubmanager\Mail\MailTaskLabel::class . '->generateMailTaskTitle',
    'tstamp'                   => 'tstamp',
    'crdate'                   => 'crdate',
    'cruser_id'                => 'cruser_id',
    'versioningWS'             => false,
    'delete'                   => 'deleted',
    'enablecolumns'            => [
      'disabled'  => 'hidden',
    ],
    'searchFields'             => 'mail_to',
    'iconfile'                 => 'EXT:clubmanager/Resources/Public/Icons/tx_clubmanager_domain_model_mail_task.svg',
  ],
  'types'     => [
    '1' => ['showitem' => 'hidden, send_state, priority_level, open_tries, generator_class, generator_arguments, processed_time, error_time, error_message'],
  ],
  'palettes'  => [
    '1' => ['showitem' => ''],
  ],
  'columns'   => [
    'hidden' => [
      'exclude' => true,
      'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.visible',
      'config' => [
        'type' => 'check',
        'renderType' => 'checkboxToggle',
        'items' => [
          [
            0 => '',
            1 => '',
            'invertStateDisplay' => true
          ]
        ],
      ],
    ],
    'send_state' => [
      'exclude' => 0,
      'label'   => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_mail_task.send_state',
      'config'  => [
        'type'       => 'select',
        'renderType' => 'selectSingle',
        'items'      => [
          ['LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_mail_task.send_state.' . Quicko\Clubmanager\Domain\Model\Mail\Task::SEND_STATE_WILL_SEND, Quicko\Clubmanager\Domain\Model\Mail\Task::SEND_STATE_WILL_SEND],
          ['LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_mail_task.send_state.' . Quicko\Clubmanager\Domain\Model\Mail\Task::SEND_STATE_DONE, Quicko\Clubmanager\Domain\Model\Mail\Task::SEND_STATE_DONE],
          ['LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_mail_task.send_state.' . Quicko\Clubmanager\Domain\Model\Mail\Task::SEND_STATE_STOPPED, Quicko\Clubmanager\Domain\Model\Mail\Task::SEND_STATE_STOPPED],
        ],
        'default'    => 0,
        'size'       => 1,
        'maxitems'   => 1,
        'eval'       => '',
      ],
    ],
    'priority_level' => [
      'exclude' => 0,
      'label'   => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_mail_task.priority_level',
      'config'  => [
        'type'       => 'select',
        'renderType' => 'selectSingle',
        'items'      => [
          ['LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_mail_task.priority_level.' . Quicko\Clubmanager\Domain\Model\Mail\Task::PRIORITY_LEVEL_MIN, Quicko\Clubmanager\Domain\Model\Mail\Task::PRIORITY_LEVEL_MIN],
          ['LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_mail_task.priority_level.' . Quicko\Clubmanager\Domain\Model\Mail\Task::PRIORITY_LEVEL_MEDIUM, Quicko\Clubmanager\Domain\Model\Mail\Task::PRIORITY_LEVEL_MEDIUM],
          ['LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_mail_task.priority_level.' . Quicko\Clubmanager\Domain\Model\Mail\Task::PRIORITY_LEVEL_HIGHT, Quicko\Clubmanager\Domain\Model\Mail\Task::PRIORITY_LEVEL_HIGHT],
        ],
        'default'    => 0,
        'size'       => 1,
        'maxitems'   => 1,
        'eval'       => '',
      ],
    ],    
    'generator_class' => [
      'exclude' => true,
      'label'   => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_mail_task.generator_class',
      'config'  => [
        'type' => 'input',
        'size' => 30,
        'eval' => 'trim',
      ],
    ],
    'generator_arguments' => [
      'exclude' => true,
      'label'   => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_mail_task.generator_arguments',
      'config'  => [
        'type' => 'text',
        'cols' => 320,
        'rows' => 15
      ],
    ],
    'processed_time' => [
      'exclude' => true,
      'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_mail_task.processed_time',
      'config' => [
        'type' => 'input',
        'renderType' => 'inputDateTime',
        'dbType' => 'datetime',
        'default' => null,
        'eval' => 'datetime',
      ],
    ],
    'error_time' => [
      'exclude' => true,
      'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_mail_task.error_time',
      'config' => [
        'type' => 'input',
        'renderType' => 'inputDateTime',
        'dbType' => 'datetime',
        'default' => null,
        'eval' => 'datetime',
      ],
    ],
    'error_message' => [
      'exclude' => true,
      'label'   => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_mail_task.error_message',
      'config'  => [
        'type' => 'text',
        'cols' => 320,
        'rows' => 15
      ],
    ],
    'open_tries' => [
      'exclude' => true,
      'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_mail_task.open_tries',
      'config' => [
        'type' => 'input',
        'eval' => 'trim,int',
        'size' => 10,
        'range' => [
          'lower' => 0,
          'upper' => 10,
        ],
        'default' => 2,
        'slider' => [
          'step' => 1,
          'width' => 200,
        ],
      ],
    ],

  ],
];
