<?php

defined('TYPO3') or exit;

return [
  'ctrl' => [
    'title' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_memberstatuschange',
    'label' => 'effective_date',
    'label_alt' => 'state',
    'label_alt_force' => true,
    'tstamp' => 'tstamp',
    'crdate' => 'crdate',
    'sortby' => 'sorting',
    'delete' => 'deleted',
    'enablecolumns' => [
      'disabled' => 'hidden',
    ],
    'iconfile' => 'EXT:clubmanager/Resources/Public/Icons/member.svg',
    'default_sortby' => 'effective_date DESC',
  ],
  'types' => [
    '1' => [
      'showitem' => 'state, effective_date, note, processed, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, hidden',
    ],
  ],
  'columns' => [
    'hidden' => [
      'exclude' => true,
      'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.visible',
      'config' => [
        'type' => 'check',
        'renderType' => 'checkboxToggle',
        'default' => 0,
        'items' => [
          [
            'label' => '',
            'invertStateDisplay' => true,
          ],
        ],
      ],
    ],
    'member' => [
      'config' => [
        'type' => 'passthrough',
      ],
    ],
    'state' => [
      'exclude' => true,
      'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.state',
      'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'items' => [
          ['label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.state.0', 'value' => 0],
          ['label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.state.1', 'value' => 1],
          ['label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.state.2', 'value' => 2],
          ['label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.state.3', 'value' => 3],
          ['label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.state.4', 'value' => 4],
        ],
        'required' => true,
      ],
    ],
    'effective_date' => [
      'exclude' => true,
      'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_memberstatuschange.effective_date',
      'config' => [
        'type' => 'datetime',
        'format' => 'date',
        'required' => true,
        'default' => 0,
      ],
    ],
    'note' => [
      'exclude' => true,
      'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_memberstatuschange.note',
      'config' => [
        'type' => 'text',
        'rows' => 3,
      ],
    ],
    'processed' => [
      'exclude' => true,
      'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_memberstatuschange.processed',
      'config' => [
        'type' => 'check',
        'renderType' => 'checkboxToggle',
        'readOnly' => true,
      ],
    ],
    'created_by' => [
      'config' => [
        'type' => 'passthrough',
      ],
    ],
  ],
];

