<?php

defined('TYPO3') or exit;

$ll = 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_membershiptype';

return [
    'ctrl' => [
        'title' => $ll,
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'sortby' => 'sorting',
        'delete' => 'deleted',
        // Bewusst kein `hidden`: Deaktivierte Typen bleiben an bestehenden Mitgliedern sichtbar
        // und werden nur in Auswahllisten für neue Zuordnungen ausgeblendet (Feld `inactive`).
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'translationSource' => 'l10n_source',
        'searchFields' => 'title',
        'iconfile' => 'EXT:clubmanager/Resources/Public/Icons/tx_clubmanager_domain_model_membershiptype.svg',
        'security' => [
            'ignorePageTypeRestriction' => true,
        ],
    ],
    'types' => [
        '1' => [
            'showitem' => '
                title, person_type, inactive,
                member_levels,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
                    sys_language_uid, l10n_parent',
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
        'person_type' => [
            'label' => $ll . '.person_type',
            'description' => $ll . '.person_type.description',
            'l10n_mode' => 'exclude',
            'l10n_display' => 'defaultAsReadonly',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.person_type.natural', 'value' => Quicko\Clubmanager\Domain\Model\Member::PERSON_TYPE_NATURAL],
                    ['label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.person_type.juridical', 'value' => Quicko\Clubmanager\Domain\Model\Member::PERSON_TYPE_JURIDICAL],
                ],
                'default' => Quicko\Clubmanager\Domain\Model\Member::PERSON_TYPE_NATURAL,
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
        'member_levels' => [
            'label' => $ll . '.member_levels',
            'description' => $ll . '.member_levels.description',
            'l10n_mode' => 'exclude',
            'l10n_display' => 'defaultAsReadonly',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'tx_clubmanager_domain_model_memberlevel',
                'foreign_table_where' => 'AND {#tx_clubmanager_domain_model_memberlevel}.{#sys_language_uid} IN (-1,0)',
                'MM' => 'tx_clubmanager_memberlevel_membershiptype_mm',
                'MM_opposite_field' => 'membership_types',
                'size' => 6,
                'maxitems' => 99,
            ],
        ],
    ],
];
