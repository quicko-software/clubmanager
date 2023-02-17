<?php

defined('TYPO3') or die();


return [
    'ctrl'      => [
        'title'                    => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_socialmedia',
        'label'                    => 'url',
        'tstamp'                   => 'tstamp',
        'crdate'                   => 'crdate',
        'cruser_id'                => 'cruser_id',
        'sortby'                   => 'sorting',
        'versioningWS'             => true,
        'hideTable'                => true,
        'delete'                   => 'deleted',
        'enablecolumns'            => [
            'disabled'  => 'hidden',
            'starttime' => 'starttime',
            'endtime'   => 'endtime',
        ],
        'searchFields'             => 'type,url',
        'iconfile'                 => 'EXT:clubmanager/Resources/Public/Icons/tx_clubmanager_domain_model_socialmedia.svg',
    ],
    'types'     => [
        '1' => ['showitem' => 'hidden, type, url'],
    ],
    'palettes'  => [
        '1' => ['showitem' => ''],
    ],
    'columns'   => [
        'starttime' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.starttime',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'datetime,int',
                'default' => 0
            ],
            'l10n_mode' => 'exclude',
            'l10n_display' => 'defaultAsReadonly'
        ],
        'endtime' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.endtime',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'datetime,int',
                'default' => 0,
                'range' => [
                    'upper' => mktime(0, 0, 0, 1, 1, 2038)
                ]
            ],
            'l10n_mode' => 'exclude',
            'l10n_display' => 'defaultAsReadonly'
        ],
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
        'type' => [
            'exclude' => true,
            'label'   => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_socialmedia.type',
            'config'  => [
                'type'       => 'select',
                'renderType' => 'selectSingle',
                'items'      => [
                    ['LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_socialmedia.type.0', \Quicko\Clubmanager\Domain\Model\Socialmedia::TYPE_FACEBOOK],
                    ['LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_socialmedia.type.1', \Quicko\Clubmanager\Domain\Model\Socialmedia::TYPE_INSTAGRAM],
                    ['LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_socialmedia.type.2', \Quicko\Clubmanager\Domain\Model\Socialmedia::TYPE_YOUTUBE],
                    ['LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_socialmedia.type.3', \Quicko\Clubmanager\Domain\Model\Socialmedia::TYPE_TWITTER],
                ],
                'size'       => 1,
                'maxitems'   => 1,
                'eval'       => '',
            ],
        ],
        'url' => [
            'exclude' => true,
            'label'   => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_socialmedia.url',
            'config'  => [
                'type' => 'input',
                'renderType' => 'inputLink',
            ],
        ],
        'location' => [
            'exclude' => true,
            'label' => '', /* SHALL_NOT_BE_VISIBLE */
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_clubmanager_domain_model_location',
            ],
        ],        
    ],
];
