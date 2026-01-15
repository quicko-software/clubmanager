<?php

defined('TYPO3') or exit;

use Quicko\Clubmanager\Domain\Model\Member;
use Quicko\Clubmanager\Domain\Model\MemberJournalEntry;

return [
  'ctrl' => [
    'title' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_memberjournalentry',
    'label' => 'entry_date',
    'label_alt' => 'entry_type, target_state, effective_date',
    'label_alt_force' => true,
    'type' => 'entry_type',
    'typeicon_column' => 'entry_type',
    'typeicon_classes' => [
      'cancellation_request' => 'actions-close',
      'status_change' => 'actions-document-open',
      'level_change' => 'actions-move-up',
    ],
    'tstamp' => 'tstamp',
    'crdate' => 'crdate',
    'delete' => 'deleted',
    'enablecolumns' => [
      'disabled' => 'hidden',
    ],
    'default_sortby' => 'entry_date DESC',
    'iconfile' => 'EXT:clubmanager/Resources/Public/Icons/journal.svg',
  ],
  'types' => [
    'cancellation_request' => [
      'showitem' => '
        entry_type, --palette--;;entry_meta,
        --palette--;;cancellation_data,
        note,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, hidden
      ',
    ],
    'status_change' => [
      'showitem' => '
        entry_type, --palette--;;entry_meta,
        --palette--;;status_data,
        note,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, hidden
      ',
      'columnsOverrides' => [
        'effective_date' => [
          'config' => [
            'required' => true,
          ],
        ],
      ],
    ],
    'level_change' => [
      'showitem' => '
        entry_type, --palette--;;entry_meta,
        --palette--;;level_data,
        note,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, hidden
      ',
      'columnsOverrides' => [
        'effective_date' => [
          'config' => [
            'required' => true,
          ],
        ],
        'new_level' => [
          'config' => [
            'required' => true,
          ],
        ],
      ],
    ],
  ],
  'palettes' => [
    'entry_meta' => [
      'showitem' => 'entry_date, creator_type',
    ],
    'cancellation_data' => [
      'showitem' => 'processed',
    ],
    'status_data' => [
      'showitem' => 'target_state, --linebreak--, effective_date, processed',
    ],
    'level_data' => [
      'showitem' => 'old_level, new_level, --linebreak--, effective_date, processed',
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
          ['label' => '', 'invertStateDisplay' => true],
        ],
      ],
    ],
    'member' => [
      'config' => [
        'type' => 'passthrough',
      ],
    ],
    'entry_type' => [
      'exclude' => true,
      'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_memberjournalentry.entry_type',
      'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'items' => [
          ['label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_memberjournalentry.entry_type.cancellation_request', 'value' => 'cancellation_request'],
          ['label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_memberjournalentry.entry_type.status_change', 'value' => 'status_change'],
          ['label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_memberjournalentry.entry_type.level_change', 'value' => 'level_change'],
        ],
        'default' => 'status_change',
      ],
    ],
    'entry_date' => [
      'exclude' => true,
      'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_memberjournalentry.entry_date',
      'config' => [
        'type' => 'datetime',
        'format' => 'datetime',
        'required' => true,
        'default' => 0,
      ],
    ],
    'creator_type' => [
      'exclude' => true,
      'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_memberjournalentry.creator_type',
      'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'items' => [
          ['label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_memberjournalentry.creator_type.system', 'value' => 0],
          ['label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_memberjournalentry.creator_type.backend', 'value' => 1],
          ['label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_memberjournalentry.creator_type.member', 'value' => 2],
        ],
        'default' => 1,
      ],
    ],
    'effective_date' => [
      'exclude' => true,
      'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_memberjournalentry.effective_date',
      'config' => [
        'type' => 'datetime',
        'format' => 'date',
        'default' => 0,
      ],
    ],
    'processed' => [
      'exclude' => true,
      'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_memberjournalentry.processed',
      'config' => [
        'type' => 'datetime',
        'format' => 'datetime',
      ],
    ],
    'note' => [
      'exclude' => true,
      'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_memberjournalentry.note',
      'config' => [
        'type' => 'text',
        'rows' => 5,
      ],
    ],
    'target_state' => [
      'displayCond' => 'FIELD:entry_type:=:status_change',
      'exclude' => true,
      'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.state',
      'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'items' => [
          ['label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.state.1', 'value' => Member::STATE_APPLIED],
          ['label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.state.2', 'value' => Member::STATE_ACTIVE],
          ['label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.state.3', 'value' => Member::STATE_SUSPENDED],
          ['label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.state.4', 'value' => Member::STATE_CANCELLED],
        ],
        'required' => true,
      ],
    ],
    'old_level' => [
      'displayCond' => 'FIELD:entry_type:=:level_change',
      'exclude' => true,
      'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_memberjournalentry.old_level',
      'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'items' => [
          ['label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.level.0', 'value' => Member::LEVEL_BASE],
          ['label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.level.10', 'value' => Member::LEVEL_BRONZE],
          ['label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.level.20', 'value' => Member::LEVEL_SILVER],
          ['label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.level.30', 'value' => Member::LEVEL_GOLD],
        ],
      ],
    ],
    'new_level' => [
      'displayCond' => 'FIELD:entry_type:=:level_change',
      'exclude' => true,
      'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_memberjournalentry.new_level',
      'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'items' => [
          ['label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.level.0', 'value' => Member::LEVEL_BASE],
          ['label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.level.10', 'value' => Member::LEVEL_BRONZE],
          ['label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.level.20', 'value' => Member::LEVEL_SILVER],
          ['label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.level.30', 'value' => Member::LEVEL_GOLD],
        ],
        'required' => true,
      ],
    ],
  ],
];


