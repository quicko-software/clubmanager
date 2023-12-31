<?php

defined('TYPO3') or die();


return [
    'ctrl'      => [
        'title'                    => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member',
        'label'                    => 'ident',
        'label_alt'                => 'lastname,firstname,city',
        'label_alt_force'          => true,
        'tstamp'                   => 'tstamp',
        'crdate'                   => 'crdate',
        'cruser_id'                => 'cruser_id',
        'sortby'                   => 'sorting',
        'versioningWS'             =>  true,
        'delete'                   => 'deleted',
        'enablecolumns'            => [
            'disabled'  => 'hidden',
            'starttime' => 'starttime',
            'endtime'   => 'endtime',
        ],
        'searchFields' => 'searchfield,ident,email,phone,telefax,title,firstname,midname,lastname,company,street,zip,city,iban,bic,account,alt_billing_name,alt_billing_street,alt_billing_zip,alt_billing_city,customfield1,customfield2,customfield3,customfield4,customfield5,customfield5',
        'iconfile'     => 'EXT:clubmanager/Resources/Public/Icons/member.svg',
    ],
    'types'     => [
        '1' => [
            'showitem' =>
            '   --palette--;LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.palette.membership;membership,
                --palette--;LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.palette.member;member,
                --palette--;LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.palette.address;address,
		        --div--;LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.tab.locations,
                    main_location,
                    sub_locations,
		        --div--;LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.tab.bank, 
                    --palette--;LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.palette.account; bank_account,
                    --palette--;LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.palette.alt_address; alt_address,
                --div--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_category.tabs.category, categories,
                --div--;LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.tab.customfields, customfield1, customfield2, customfield3, customfield4, customfield5, customfield6,
		        --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access, hidden, crdate',
        ],
    ],
    'palettes'  => [
        '1'           => ['showitem' => ''],
        'membership' => [
            'showitem' => '
                ident, --linebreak--, 
                state, level, --linebreak--,
                reduced_rate, cancellation_wish, --linebreak--,
                starttime, endtime,--linebreak--,
                email, phone, telefax,--linebreak--,
                feuser
            '
        ],
        'member' => [
            'showitem' => '
                person_type, nationality,--linebreak--,
                salutation, title, --linebreak--,
                firstname, midname, --linebreak--, 
                lastname,  dateofbirth
            '
        ],
        'address' => [
            'showitem' => '
                company, add_address_info, --linebreak--,
                street, --linebreak--,
                zip, city, --linebreak--,
                country, federal_state
            '
        ],
        'bank_account' => [
            'showitem' => '
                direct_debit,--linebreak--,
                account,--linebreak--,
                iban,bic
            '
        ],
        'alt_address' => [
            'canNotCollapse' => 1,
            'showitem'       => '
			    alt_billing_name,alt_billing_street, --linebreak--,
                alt_billing_zip,alt_billing_city,alt_billing_country
			'
        ],
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
            ]
        ],
        'starttime' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.starttime',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'date,int',
                'default' => 0
            ],
            'l10n_mode' => 'exclude',
            'l10n_display' => 'defaultAsReadonly'
        ],
        'endtime' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.endtime',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'date,int',
                'default' => 0,
                'range' => [
                    'upper' => mktime(0, 0, 0, 1, 1, 2038)
                ]
            ],
            'l10n_mode' => 'exclude',
            'l10n_display' => 'defaultAsReadonly'
        ],

        'crdate' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.crdate',
            'config' => [
                'type' => 'input',
                'eval' => 'datetime',
                'renderType' => 'inputDateTime',
                'readOnly' => true,
            ]
        ],

        'ident'            => [
            'exclude' => true,
            'label'   => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.ident',
            'config'  => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,unique',
            ],
        ],

        'categories' => [
            'exclude' => true,
            'label'   => 'LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_category.categories',
            'config'  => [
                'type' => 'category',
                'treeConfig' => [
                    'startingPoints' => \Quicko\Clubmanager\Utils\SettingUtils::get('clubmanager', 'uidCategoryMember'),
                    'appearance' => [
                        'nonSelectableLevels' => '0,1',
                    ]
                ],
            ],
        ],

        'person_type' => [
            'exclude' => true,
            'label'   => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.person_type',
            'config'  => [
                'type'       => 'select',
                'renderType' => 'selectSingle',
                'items'      => [
                    ['LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.person_type.natural', \Quicko\Clubmanager\Domain\Model\Member::PERSON_TYPE_NATURAL],
                    ['LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.person_type.juridical', \Quicko\Clubmanager\Domain\Model\Member::PERSON_TYPE_JURIDICAL],
                ],
                'size'       => 1,
                'maxitems'   => 1,
                'eval'       => 'required',
            ],
        ],
        'nationality' => [
            'exclude' => true,
            'label'   => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.nationality',
            'config'  => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],

        'salutation' => [
            'exclude' => true,
            'label'   => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.salutation',
            'config'  => [
                'type'       => 'select',
                'renderType' => 'selectSingle',
                'items'      => [
                    ['LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.salutation.0', \Quicko\Clubmanager\Domain\Model\Member::SALUTATION_OTHER],
                    ['LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.salutation.1', \Quicko\Clubmanager\Domain\Model\Member::SALUTATION_MALE],
                    ['LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.salutation.2', \Quicko\Clubmanager\Domain\Model\Member::SALUTATION_FEMALE],
                ],
                'size'       => 1,
                'maxitems'   => 1,
                'eval'       => 'required',
            ],
        ],


        'title' => [
            'exclude' => true,
            'label'   => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.title',
            'config'  => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'firstname'        => [
            'exclude' => true,
            'label'   => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.firstname',
            'config'  => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'midname'          => [
            'exclude' => true,
            'label'   => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.midname',
            'config'  => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'lastname'         => [
            'exclude' => true,
            'label'   => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.lastname',
            'config'  => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'company'          => [
            'exclude' => true,
            'label'   => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.company',
            'config'  => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'dateofbirth' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.dateofbirth',
            'config' => [
                'type' => 'input',
                'default' => 0,
                'size' => 30,
                'eval' => 'date,int',
                'renderType' => 'inputDateTime',
            ]
        ],

        'email'          => [
            'exclude' => true,
            'label'   => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.email',
            'config'  => [
                'type' => 'input',
                'size' => 20,
                'eval' => 'trim,email',
            ],
        ],
        'phone'          => [
            'exclude' => true,
            'label'   => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.phone',
            'config'  => [
                'type' => 'input',
                'size' => 20,
                'eval' => 'trim',
            ],
        ],
        'telefax'          => [
            'exclude' => true,
            'label'   => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.telefax',
            'config'  => [
                'type' => 'input',
                'size' => 20,
                'eval' => 'trim',
            ],
        ],

        'street'           => [
            'exclude' => true,
            'label'   => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.street',
            'config'  => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'add_address_info' => [
            'exclude' => true,
            'label'   => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.add_address_info',
            'config'  => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'zip'              => [
            'exclude' => true,
            'label'   => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.zip',
            'config'  => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'city'             => [
            'exclude' => true,
            'label'   => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.city',
            'config'  => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'country'          => [
            'exclude' => true,
            'label'   => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.country',
            'config'  => [
                'type'                => 'select',
                'renderType'          => 'selectSingle',
                'foreign_table'       => 'static_countries',
                'foreign_table_where' => 'ORDER BY static_countries.cn_short_local',
                'size'                => 1,
                'default'             => 54,
                'minitems'            => 0,
                'maxitems'            => 1,
            ],
        ],
        'federal_state'       => [
            'exclude' => 0,
            'label'   => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.federal_state',
            'config'  => [
                'type'       => 'select',
                'renderType' => 'selectSingle',
                'items'      => \Quicko\Clubmanager\Domain\Helper\States::getStates(),
                'size'       => 1,
                'minitems'   => 0,
                'maxitems'   => 1,
            ],
        ],

        'state' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.state',
            'config'  => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'size'       => 1,
                'items' => [
                    ['LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.state.0', \Quicko\Clubmanager\Domain\Model\Member::STATE_UNSET],
                    ['LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.state.1', \Quicko\Clubmanager\Domain\Model\Member::STATE_APPLIED],
                    ['LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.state.2', \Quicko\Clubmanager\Domain\Model\Member::STATE_ACTIVE],
                    ['LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.state.3', \Quicko\Clubmanager\Domain\Model\Member::STATE_SUSPENDED],
                    ['LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.state.4', \Quicko\Clubmanager\Domain\Model\Member::STATE_CANCELLED],
                ],
            ],
        ],
        'cancellation_wish' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.cancellation_wish',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'items' => [
                    [
                        0 => '',
                        1 => '',
                    ]
                ],
            ]
        ],
        'reduced_rate' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.reduced_rate',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'items' => [
                    [
                        0 => '',
                        1 => '',
                    ]
                ],
            ]
        ],
        'level'           => [
            'exclude' => true,
            'label'   => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.level',
            'config'  => [
                'type'       => 'select',
                'renderType' => 'selectSingle',
                'items'      => [
                    ['LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.level.0', \Quicko\Clubmanager\Domain\Model\Member::LEVEL_BASE],
                    ['LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.level.10', \Quicko\Clubmanager\Domain\Model\Member::LEVEL_BRONZE],
                    ['LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.level.20', \Quicko\Clubmanager\Domain\Model\Member::LEVEL_SILVER],
                    ['LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.level.30', \Quicko\Clubmanager\Domain\Model\Member::LEVEL_GOLD],
                ],
                'size'       => 1,
                'maxitems'   => 1,
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
                        0 => '',
                        1 => '',
                    ]
                ],
            ]
        ],
        'iban'             => [
            'exclude'     => true,
            'displayCond' => 'FIELD:direct_debit:=:1',
            'label'       => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.iban',
            'config'      => [
                'type'        => 'input',
                'size'        => 30,
                'eval'        => 'trim,required,' . \Quicko\Clubmanager\Evaluation\IbanEvaluation::class
            ],
        ],
        'bic'              => [
            'exclude'     => true,
            'displayCond' => 'FIELD:direct_debit:=:1',
            'label'       => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.bic',
            'config'      => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required,' . \Quicko\Clubmanager\Evaluation\BicEvaluation::class
            ],
        ],
        'account'          => [
            'exclude'     => true,
            'displayCond' => 'FIELD:direct_debit:=:1',
            'label'       => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.account',
            'config'      => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required',
            ],
        ],
        'feuser'             => [
            'exclude' => true,
            'l10n_mode' => 'exclude',
            'l10n_display' => 'defaultAsReadonly',
            'label'   => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.user',
            'config'  => [
                'type'          => 'inline',
                'foreign_table' => 'fe_users',
                'foreign_field' => 'clubmanager_member',
                'maxitems'      => 1,
                'appearance'    => [
                    'collapseAll'                     => true,
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
                            ]
                        ],
                        'usergroup' => [
                            'config' => [
                                'default' => \Quicko\Clubmanager\Utils\SettingUtils::get('clubmanager', 'defaultFeUserGroupUid'),
                             ],
                        ]
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
                    'kind' => 0
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
                    'kind' => 1
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
                            ]
                        ]
                    ],
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
        'alt_billing_city'         => [
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
            'config'  => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'static_countries',
                'foreign_table_where' => 'ORDER BY static_countries.cn_short_local',
                'size' => 1,
                'default' => 54,
                'minitems' => 0,
                'maxitems' => 1,
                'items' => [
                    ['', ''],
                ],
            ],
        ],
        'customfield1' => [
            'exclude' => true,
            'label'   => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.customfield1',
            'config'  => [
                'type' => 'input',
                'eval' => 'trim',
            ],
        ],
        'customfield2' => [
            'exclude' => true,
            'label'   => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.customfield2',
            'config'  => [
                'type' => 'input',
                'eval' => 'trim',
            ],
        ],
        'customfield3' => [
            'exclude' => true,
            'label'   => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.customfield3',
            'config'  => [
                'type' => 'input',
                'eval' => 'trim',
            ],
        ],
        'customfield4' => [
            'exclude' => true,
            'label'   => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.customfield4',
            'config'  => [
                'type' => 'input',
                'eval' => 'trim',
            ],
        ],
        'customfield5' => [
            'exclude' => true,
            'label'   => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.customfield5',
            'config'  => [
                'type' => 'input',
                'eval' => 'trim',
            ],
        ],
        'customfield6' => [
            'exclude' => true,
            'label'   => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.customfield6',
            'config'  => [
                'type' => 'input',
                'eval' => 'trim',
            ],
        ],

    ],
];
