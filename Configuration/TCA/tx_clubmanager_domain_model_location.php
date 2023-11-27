<?php

defined('TYPO3') or die();


return [
    'ctrl' => [
        'title' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_location',
        'label' => 'lastname,firstname,company,city',
        'label_alt' => 'lastname,firstname,company,street,zip,city',
        'label_alt_force' => true,
        'hideTable' => true, // displayed inline within member
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'sortby' => 'sorting',
        'versioningWS' =>  true,
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],
        'searchFields' => 'label,title,firstname,midname,lastname,info,company,street,zip,city,latitude,longitude,image,youtube_video,phone,mobile,fax,email,website,categories,country,',
        'iconfile' => 'EXT:clubmanager/Resources/Public/Icons/location.svg',
    ],
    'types'     => [
        '1' => [
            'showitem' =>
            'label, slug, 
            --palette--;LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_location.palette.address;address, 
            --palette--;LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_location.palette.person;person,
		--div--;LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_location.tab.geography, --palette--;Geodaten;geodata,
		--div--;LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_location.tab.meta, info, image, youtube_video, categories, 
		--div--;LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_location.tab.contacts, phone, mobile, fax, email, website,
        --div--;LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_location.tab.socialmedia, socialmedia,
		--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access, starttime, endtime,parent',
        ],
    ],
    'palettes'  => [
        'person' => [
            'showitem' => '
                salutation, title, --linebreak--,
                firstname, midname, --linebreak--, lastname
            '
        ],
        'address' => [
            'showitem' => '
                company, add_address_info,--linebreak--,
                street, --linebreak--,
                zip, city, --linebreak--,
                country, state
            '
        ],
        'geodata' => [
            'showitem' => '
                longitude, latitude, search_location
            '
        ]
    ],

    'columns'   => [
        /* config is used for dashboard */
        'hidden' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_location.hidden',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'items' => [
                    [
                        0 => '',
                        1 => '',
                        'invertStateDisplay' => false
                    ]
                ],
            ]
        ],
        'tstamp' => [
            'label' => 'tstamp',
            'config' => [
                'type' => 'passthrough',
            ]
        ],
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

        'salutation'           => [
            'exclude' => true,
            'label'   => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.salutation',
            'config'  => [
                'type'       => 'select',
                'renderType' => 'selectSingle',
                'items'      => [
                    ['LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.salutation.other', \Quicko\Clubmanager\Domain\Model\Member::SALUTATION_OTHER],
                    ['LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.salutation.male', \Quicko\Clubmanager\Domain\Model\Member::SALUTATION_MALE],
                    ['LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_member.salutation.female', \Quicko\Clubmanager\Domain\Model\Member::SALUTATION_FEMALE],
                ],
                'size'       => 1,
                'maxitems'   => 1,
                'eval'       => 'required',
            ],
        ],
        'slug' => [
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_location.slug',
            'config' => [
                'type' => 'slug',
                'generatorOptions' => [
                    'fields' => ['firstname','lastname','company','city'],
                    'fieldSeparator' => '-',
                    'prefixParentPageSlug' => false,
                    'replacements' => [
                        '/' => '',
                    ],
                ],
                'appearance' => [
                    'prefix' => \Quicko\Clubmanager\FormEngine\EmptySlugPrefix::class . '->getPrefix'
                 ],
                'fallbackCharacter' => '-',
                'eval' => 'uniqueInSite',
                'default' => ''
            ],
        ],
        'title'            => [
            'exclude' => 0,
            'label'   => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_location.title',
            'config'  => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'firstname'        => [
            'exclude' => 0,
            'label'   => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_location.firstname',
            'config'  => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'midname'          => [
            'exclude' => 0,
            'label'   => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_location.midname',
            'config'  => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'lastname'         => [
            'exclude' => 0,
            'label'   => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_location.lastname',
            'config'  => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],

        'info'             => [
            'exclude'       => 0,
            'label'         => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_location.info',
            'config'        => [
                'type' => 'text',
                'enableRichtext' => true,
                'eval' => 'trim'
            ],
        ],

        'company'          => [
            'exclude' => 0,
            'label'   => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_location.company',
            'config'  => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'street'           => [
            'exclude' => 0,
            'label'   => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_location.street',
            'config'  => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'add_address_info'         => [
            'exclude' => 0,
            'label'   => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_location.add_address_info',
            'config'  => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'zip'              => [
            'exclude' => 0,
            'label'   => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_location.zip',
            'config'  => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'city'             => [
            'exclude' => 0,
            'label'   => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_location.city',
            'config'  => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'country'          => [
            'exclude' => 0,
            'label'   => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_location.country',
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
        'state'       => [
            'exclude' => 0,
            'label'   => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_location.state',
            'config'  => [
                'type'       => 'select',
                'renderType' => 'selectSingle',
                'items'      => \Quicko\Clubmanager\Domain\Helper\States::getStates(),
                'size'       => 1,
                'minitems'   => 0,
                'maxitems'   => 1,
            ],
        ],

        'latitude'         => [
            'exclude' => 0,
            'label'   => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_location.latitude',
            'config'  => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'longitude'        => [
            'exclude' => 0,
            'label'   => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_location.longitude',
            'config'  => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'search_location' => [
            'exclude' => true,
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_location.search_gps_coordinates',
            'config' => [
                'type' => 'user',
                'renderType' => 'SearchLocation',
                'readOnly' => 1,
                'mapping' => [
                    "zip", "city", "street"
                ],
                'target' => [
                    'latitude' => 'lat',
                    'longitude' => 'lon'
                ]
            ],
        ],


        'image' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_location.image',
            'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
                'image',
                [
                    'appearance' => [
                        'createNewRelationLinkTitle' => 'LLL:EXT:cms/locallang_ttc.xlf:images.addFileReference'
                    ],
                    'overrideChildTca' => [
                        'types' => [
                            '0' => [
                                'showitem' => '
                                    --palette--;;imageoverlayPalette,
                                    --palette--;;filePalette'
                            ],
                            \TYPO3\CMS\Core\Resource\File::FILETYPE_TEXT => [
                                'showitem' => '
                                    --palette--;;imageoverlayPalette,
                                    --palette--;;filePalette'
                            ],
                            \TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => [
                                'showitem' => '
                                    --palette--;;imageoverlayPalette,
                                    --palette--;;filePalette'
                            ],
                            \TYPO3\CMS\Core\Resource\File::FILETYPE_AUDIO => [
                                'showitem' => '
                                    --palette--;;audioOverlayPalette,
                                    --palette--;;filePalette'
                            ],
                            \TYPO3\CMS\Core\Resource\File::FILETYPE_VIDEO => [
                                'showitem' => '
                                    --palette--;;videoOverlayPalette,
                                    --palette--;;filePalette'
                            ],
                            \TYPO3\CMS\Core\Resource\File::FILETYPE_APPLICATION => [
                                'showitem' => '
                                    --palette--;;imageoverlayPalette,
                                    --palette--;;filePalette'
                            ]
                        ],
                    ],
                    'minitems' => 0,
                    'maxitems' => 1,
                    'foreign_match_fields' => [
                        'fieldname' => 'image',
                        'tablenames' => 'tx_clubmanager_domain_model_location',
                        'table_local' => 'sys_file',
                    ],
                ],
                $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']

            ),
        ],
        'phone'          => [
            'exclude' => 0,
            'label'   => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_location.phone',
            'config'  => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'mobile'          => [
            'exclude' => 0,
            'label'   => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_location.mobile',
            'config'  => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'fax'          => [
            'exclude' => 0,
            'label'   => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_location.fax',
            'config'  => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'email'          => [
            'exclude' => 0,
            'label'   => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_location.email',
            'config'  => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,email',
            ],
        ],
        'website'          => [
            'exclude' => 0,
            'label'   => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_location.website',
            'config'  => [
                'type' => 'input',
                'renderType' => 'inputLink',
            ],
        ],
        'socialmedia'          => [
            'exclude' => 0,
            'label'   => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_location.socialmedia',
            'config'  => [
                'type'          => 'inline',
                'foreign_table' => 'tx_clubmanager_domain_model_socialmedia',
                'foreign_field' => 'location',
                'maxitems'      => 9999,
                'appearance'    => [
                    'collapseAll'                     => 1,
                    'levelLinksPosition'              => 'top',
                    'showSynchronizationLink'         => 1,
                    'showPossibleLocalizationRecords' => 1,
                    'showAllLocalizationLink'         => 1,
                ],
            ],
        ],
        
        'youtube_video' => [
            'exclude' => true,
            'label'   => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_location.youtube_video',
            'config'  => [
                'type' => 'input',
                'renderType' => 'inputLink',
            ],
        ],

        /// 'member' is not visible in the backend,
        /// but Extbase requires that relation information to be able
        /// to use join-queries in the repository, e.g. LocationRepository::findByCity()
        'member' => [
            'label' => '', /* SHALL_NOT_BE_VISIBLE */
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_clubmanager_domain_model_member',
            ],
        ],

        'categories' => [
            'label'   => 'LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_category.categories',
            'config'  => [
                'type' => 'category',
                'treeConfig' => [
                    'startingPoints' => \Quicko\Clubmanager\Utils\SettingUtils::get('clubmanager', 'uidCategoryLocation'),
                    'appearance' => [
                        'nonSelectableLevels' => '0',
                    ]
                ],
            ],
        ],

        'kind'             => [
            'exclude' => 0,
            'label'   => 'Type',
            'config'  => [
                'type'       => 'select',
                'renderType' => 'selectSingle',
                'readOnly'   => true,
                'items'      => [
                    // not visible, so the label is irrelevant
                    ['-- Hauptstandort --', '0'],
                    ['-- Weiterer Standort --', '1'],
                ],
                'default'    => 0,
                'size'       => 1,
                'minitems'   => 0,
                'maxitems'   => 1,
            ],
        ],
    ],
];
