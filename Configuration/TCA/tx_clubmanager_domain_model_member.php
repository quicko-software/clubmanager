<?php

defined('TYPO3') or exit;

return [
    'ctrl' => [
        'title' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member',
        'label' => 'ident',
        'label_alt' => 'lastname,firstname,city',
        'label_alt_force' => true,
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'sortby' => 'sorting',
        'versioningWS' => true,
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],
        'searchFields' => 'searchfield,ident,email,phone,telefax,title,firstname,midname,lastname,company,street,zip,city,iban,bic,account,alt_billing_name,alt_billing_street,alt_billing_zip,alt_billing_city,club_function,found_via,customfield1,customfield2,customfield3,customfield4,customfield5,customfield5',
        'iconfile' => 'EXT:clubmanager/Resources/Public/Icons/member.svg',
    ],
    'types' => [
        '1' => [
            'showitem' => '   --palette--;LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.palette.membership;membership,
                --palette--;LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.palette.member;member,
                --palette--;LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.palette.address;address,
		        --div--;LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.tab.locations,
                    main_location,
                    sub_locations,
		        --div--;LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.tab.bank, 
                    --palette--;LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.palette.account; bank_account,
                    --palette--;LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.palette.alt_address; alt_address,
                --div--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_category.tabs.category, categories,
                --div--;LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.tab.customfields, club_function, found_via, customfield1, customfield2, customfield3, customfield4, customfield5, customfield6,
                --div--;LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.tab.journal, journal_entries,
		        --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access, hidden, crdate',
        ],
    ],
    'palettes' => [
        '1' => ['showitem' => ''],
        'membership' => [
            'showitem' => '
                ident, state, --linebreak--, 
                starttime, endtime, --linebreak--,
                level, reduced_rate, --linebreak--,
                email, phone, telefax,--linebreak--,
                feuser
            ',
        ],
        'member' => [
            'showitem' => '
                person_type, nationality,--linebreak--,
                salutation, title, --linebreak--,
                firstname, midname, --linebreak--, 
                lastname,  dateofbirth
            ',
        ],
        'address' => [
            'showitem' => '
                company, add_address_info, --linebreak--,
                street, --linebreak--,
                zip, city, --linebreak--,
                country, federal_state
            ',
        ],
        'bank_account' => [
            'showitem' => '
                direct_debit,--linebreak--,
                account,--linebreak--,
                iban,bic
            ',
        ],
        'alt_address' => [
            'canNotCollapse' => 1,
            'showitem' => '
			    alt_billing_name,alt_billing_street, --linebreak--,
                alt_billing_zip,alt_billing_city,alt_billing_country, --linebreak--,
                alt_email
			',
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
        'starttime' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.starttime',
            'config' => [
                'type' => 'datetime',
                'default' => 0,
                'eval' => 'int',
                'format' => 'date',
                'readOnly' => true,
            ],
            'l10n_mode' => 'exclude',
            'l10n_display' => 'defaultAsReadonly',
        ],
        'endtime' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.endtime',
            'config' => [
                'type' => 'datetime',
                'format' => 'date',
                'eval' => 'int',
                'default' => 0,
                'range' => [
                    'upper' => mktime(0, 0, 0, 1, 1, 2038),
                ],
                'readOnly' => true,
            ],
            'l10n_mode' => 'exclude',
            'l10n_display' => 'defaultAsReadonly',
        ],

        'crdate' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.crdate',
            'config' => [
                'type' => 'datetime',
                'format' => 'datetime',
                'readOnly' => true,
            ],
        ],

        'ident' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.ident',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,unique',
            ],
        ],

        'categories' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_category.categories',
            'config' => [
                'type' => 'category',
                'treeConfig' => [
                    'startingPoints' => Quicko\Clubmanager\Utils\SettingUtils::get('clubmanager', 'uidCategoryMember'),
                    'appearance' => [
                        'nonSelectableLevels' => '0,1',
                    ],
                ],
            ],
        ],

        'person_type' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.person_type',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.person_type.natural', 'value' => Quicko\Clubmanager\Domain\Model\Member::PERSON_TYPE_NATURAL],
                    ['label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.person_type.juridical', 'value' => Quicko\Clubmanager\Domain\Model\Member::PERSON_TYPE_JURIDICAL],
                ],
                'size' => 1,
                'maxitems' => 1,
                'required' => true,
            ],
        ],
        'nationality' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.nationality',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],

        'salutation' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.salutation',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.salutation.0', 'value' => Quicko\Clubmanager\Domain\Model\Member::SALUTATION_OTHER],
                    ['label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.salutation.1', 'value' => Quicko\Clubmanager\Domain\Model\Member::SALUTATION_MALE],
                    ['label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.salutation.2', 'value' => Quicko\Clubmanager\Domain\Model\Member::SALUTATION_FEMALE],
                ],
                'size' => 1,
                'maxitems' => 1,
                'required' => true,
            ],
        ],

        'title' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.title',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'firstname' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.firstname',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'midname' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.midname',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'lastname' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.lastname',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'company' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.company',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'dateofbirth' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.dateofbirth',
            'config' => [
                'type' => 'datetime',
                'default' => 0,
                'size' => 30,
                'eval' => 'int',
                'format' => 'date',
            ],
        ],

        'email' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.email',
            'config' => [
                'type' => 'email',
                'size' => 20,
                'eval' => 'trim,unique',
            ],
        ],
        'phone' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.phone',
            'config' => [
                'type' => 'input',
                'size' => 20,
                'eval' => 'trim',
            ],
        ],
        'telefax' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.telefax',
            'config' => [
                'type' => 'input',
                'size' => 20,
                'eval' => 'trim',
            ],
        ],

        'street' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.street',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'add_address_info' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.add_address_info',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'zip' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.zip',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'city' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.city',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'country' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.country',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'static_countries',
                'foreign_table_where' => 'ORDER BY static_countries.cn_short_local',
                'size' => 1,
                'default' => 54,
                'minitems' => 0,
                'maxitems' => 1,
            ],
        ],
        'federal_state' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.federal_state',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => Quicko\Clubmanager\Domain\Helper\States::getStates(),
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1,
            ],
        ],

        'state' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.state',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'size' => 1,
                'readOnly' => true,
                'items' => [
                    ['label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.state.0', 'value' => Quicko\Clubmanager\Domain\Model\Member::STATE_UNSET],
                    ['label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.state.1', 'value' => Quicko\Clubmanager\Domain\Model\Member::STATE_APPLIED],
                    ['label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.state.2', 'value' => Quicko\Clubmanager\Domain\Model\Member::STATE_ACTIVE],
                    ['label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.state.3', 'value' => Quicko\Clubmanager\Domain\Model\Member::STATE_SUSPENDED],
                    ['label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.state.4', 'value' => Quicko\Clubmanager\Domain\Model\Member::STATE_CANCELLED],
                ],
            ],
        ],
        'reduced_rate' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.reduced_rate',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'items' => [
                    [
                        'label' => '',
                    ],
                ],
            ],
        ],
        'level' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.level',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.level.0', 'value' => Quicko\Clubmanager\Domain\Model\Member::LEVEL_BASE],
                    ['label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.level.10', 'value' => Quicko\Clubmanager\Domain\Model\Member::LEVEL_BRONZE],
                    ['label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.level.20', 'value' => Quicko\Clubmanager\Domain\Model\Member::LEVEL_SILVER],
                    ['label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.level.30', 'value' => Quicko\Clubmanager\Domain\Model\Member::LEVEL_GOLD],
                ],
                'size' => 1,
                'maxitems' => 1,
            ],
        ],
        'direct_debit' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.direct_debit',
            'onChange' => 'reload',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'items' => [
                    [
                        'label' => '',
                    ],
                ],
            ],
        ],
        'iban' => [
            'exclude' => true,
            'displayCond' => 'FIELD:direct_debit:=:1',
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.iban',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,' . Quicko\Clubmanager\Evaluation\IbanEvaluation::class,
                'required' => true,
            ],
        ],
        'bic' => [
            'exclude' => true,
            'displayCond' => 'FIELD:direct_debit:=:1',
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.bic',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,' . Quicko\Clubmanager\Evaluation\BicEvaluation::class,
                'required' => true,
            ],
        ],
        'account' => [
            'exclude' => true,
            'displayCond' => 'FIELD:direct_debit:=:1',
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.account',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'required' => true,
            ],
        ],
        'feuser' => [
            'exclude' => true,
            'l10n_mode' => 'exclude',
            'l10n_display' => 'defaultAsReadonly',
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.user',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'fe_users',
                'foreign_field' => 'clubmanager_member',
                'maxitems' => 1,
                'appearance' => [
                    'collapseAll' => true,
                ],
                'overrideChildTca' => [
                    'types' => [
                        '0' => [
                            'showitem' => 'username,password,usergroup,lastlogin,lastreminderemailsent',
                        ],
                    ],
                    'columns' => [
                        'username' => [
                            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.username',
                        ],
                        'password' => [
                            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_user.password',
                            'config' => [
                                'eval' => 'trim,password,saltedPassword',
                            ],
                        ],
                        'usergroup' => [
                            'config' => [
                                'default' => Quicko\Clubmanager\Utils\SettingUtils::get('clubmanager', 'defaultFeUserGroupUid'),
                            ],
                        ],
                    ],
                ],
            ],
        ],

        'main_location' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.main_location',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_clubmanager_domain_model_location',
                'foreign_field' => 'member',
                'foreign_match_fields' => [
                    'kind' => 0,
                ],
                'minitems' => 0,
                'maxitems' => 1,
                'appearance' => [
                    'showSynchronizationLink' => 1,
                    'showPossibleLocalizationRecords' => 1,
                    'showAllLocalizationLink' => 1,
                ],
            ],
        ],

        'sub_locations' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.sub_locations',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_clubmanager_domain_model_location',
                'foreign_field' => 'member',
                'foreign_match_fields' => [
                    'kind' => 1,
                ],
                'minitems' => 0,
                'maxitems' => 9999,
                'appearance' => [
                    'collapseAll' => 1,
                    'showSynchronizationLink' => 1,
                    'showPossibleLocalizationRecords' => 1,
                    'showAllLocalizationLink' => 1,
                ],
                'overrideChildTca' => [
                    'columns' => [
                        'kind' => [
                            'config' => [
                                'default' => 1,
                            ],
                        ],
                    ],
                ],
            ],
        ],

        'journal_entries' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.journal_entries',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_clubmanager_domain_model_memberjournalentry',
                'foreign_field' => 'member',
                'foreign_default_sortby' => 'entry_date DESC',
                'minitems' => 0,
                'maxitems' => 9999,
                'appearance' => [
                    'collapseAll' => 1,
                    'expandSingle' => 1,
                    'showSynchronizationLink' => 1,
                ],
            ],
        ],

        'alt_billing_name' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.alt_billing_name',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'alt_billing_street' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.alt_billing_street',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'alt_billing_zip' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.alt_billing_zip',
            'config' => [
                'type' => 'input',
                'size' => 8,
                'eval' => 'trim',
            ],
        ],
        'alt_billing_city' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.alt_billing_city',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'alt_billing_country' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.alt_billing_country',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'static_countries',
                'foreign_table_where' => 'ORDER BY static_countries.cn_short_local',
                'size' => 1,
                'default' => 54,
                'minitems' => 0,
                'maxitems' => 1,
                'items' => [
                    ['value' => '', 'key' => ''],
                ],
            ],
        ],
        'alt_email' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.alt_email',
            'config' => [
                'type' => 'email',
                'size' => 20,
                'eval' => 'trim',
            ],
        ],
        'customfield1' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.customfield1',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
            ],
        ],
        'customfield2' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.customfield2',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
            ],
        ],
        'customfield3' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.customfield3',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
            ],
        ],
        'customfield4' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.customfield4',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
            ],
        ],
        'customfield5' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.customfield5',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
            ],
        ],
        'customfield6' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.customfield6',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
            ],
        ],
        'club_function' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.club_function',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
            ],
        ],
        'found_via' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.found_via',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang.xlf:tx_clubmanager_domain_model_member.found_via.0', 'value' => Quicko\Clubmanager\Domain\Model\Member::FOUND_VIA_0],
                    ['label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang.xlf:tx_clubmanager_domain_model_member.found_via.10', 'value' => Quicko\Clubmanager\Domain\Model\Member::FOUND_VIA_10],
                    ['label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang.xlf:tx_clubmanager_domain_model_member.found_via.20', 'value' => Quicko\Clubmanager\Domain\Model\Member::FOUND_VIA_20],
                    ['label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang.xlf:tx_clubmanager_domain_model_member.found_via.30', 'value' => Quicko\Clubmanager\Domain\Model\Member::FOUND_VIA_30],
                    ['label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang.xlf:tx_clubmanager_domain_model_member.found_via.40', 'value' => Quicko\Clubmanager\Domain\Model\Member::FOUND_VIA_40],
                    ['label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang.xlf:tx_clubmanager_domain_model_member.found_via.50', 'value' => Quicko\Clubmanager\Domain\Model\Member::FOUND_VIA_50],
                    ['label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang.xlf:tx_clubmanager_domain_model_member.found_via.60', 'value' => Quicko\Clubmanager\Domain\Model\Member::FOUND_VIA_60],
                    ['label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang.xlf:tx_clubmanager_domain_model_member.found_via.70', 'value' => Quicko\Clubmanager\Domain\Model\Member::FOUND_VIA_70],
                ],
                'size' => 1,
                'maxitems' => 1,
            ],
        ],
    ],
];
