<?php

defined('TYPO3') or exit;

$ll = 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_memberlevel';

return [
    'ctrl' => [
        'title' => $ll,
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'sortby' => 'sorting',
        'delete' => 'deleted',
        // Bewusst kein `hidden`, siehe Mitgliedschaftstyp: Deaktivieren über `inactive`.
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'translationSource' => 'l10n_source',
        'searchFields' => 'title',
        'iconfile' => 'EXT:clubmanager/Resources/Public/Icons/tx_clubmanager_domain_model_memberlevel.svg',
        'security' => [
            'ignorePageTypeRestriction' => true,
        ],
    ],
    'types' => [
        '1' => [
            'showitem' => '
                title, --palette--;;ranking, inactive,
                membership_types,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
                    sys_language_uid, l10n_parent',
        ],
    ],
    'palettes' => [
        'ranking' => [
            'showitem' => 'ranking, display_class',
        ],
    ],
    'columns' => [
        'title' => [
            'label' => $ll . '.title',
            'config' => [
                'type' => 'input',
                'size' => 40,
                'max' => 255,
                'required' => true,
                'eval' => 'trim',
            ],
        ],
        'ranking' => [
            'label' => $ll . '.ranking',
            'description' => $ll . '.ranking.description',
            'l10n_mode' => 'exclude',
            'l10n_display' => 'defaultAsReadonly',
            'config' => [
                'type' => 'number',
                'size' => 6,
                'default' => 0,
            ],
        ],
        'display_class' => [
            'label' => $ll . '.display_class',
            'description' => $ll . '.display_class.description',
            'l10n_mode' => 'exclude',
            'l10n_display' => 'defaultAsReadonly',
            'config' => [
                'type' => 'input',
                'size' => 20,
                'max' => 64,
                'eval' => 'trim,alphanum_x',
                'default' => '',
                'valuePicker' => [
                    'items' => [
                        ['label' => 'default', 'value' => 'default'],
                        ['label' => 'secondary', 'value' => 'secondary'],
                        ['label' => 'tertiary', 'value' => 'tertiary'],
                        ['label' => 'quaternary', 'value' => 'quaternary'],
                    ],
                ],
            ],
        ],
        'inactive' => [
            'label' => $ll . '.inactive',
            'description' => $ll . '.inactive.description',
            'l10n_mode' => 'exclude',
            'l10n_display' => 'defaultAsReadonly',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'default' => 0,
            ],
        ],
        'membership_types' => [
            'label' => $ll . '.membership_types',
            'description' => $ll . '.membership_types.description',
            'l10n_mode' => 'exclude',
            'l10n_display' => 'defaultAsReadonly',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'tx_clubmanager_domain_model_membershiptype',
                'foreign_table_where' => 'AND {#tx_clubmanager_domain_model_membershiptype}.{#sys_language_uid} IN (-1,0)',
                'MM' => 'tx_clubmanager_memberlevel_membershiptype_mm',
                'size' => 6,
                'maxitems' => 99,
            ],
        ],
    ],
];
