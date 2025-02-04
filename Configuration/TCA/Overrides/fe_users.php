<?php

defined('TYPO3') or die();

call_user_func(function () {

    $fields = [
        'clubmanager_member' => [
            'label' => '', /* SHALL_NOT_BE_VISIBLE */
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_clubmanager_domain_model_member',
                'foreign_field' => 'feuser',
            ],
        ],        
        'lastreminderemailsent' => [
            'label' => 'LLL:EXT:clubmanager/Resources/Private/Language/locallang_db.xlf:tx_clubmanager_domain_model_user.lastreminderemailsent',
            'config' => [
                'type' => 'datetime',
                'default' => 0,
                'eval' => 'int',
                'format' => 'datetime',
            ]
        ]
      
    ];

    $GLOBALS['TCA']['fe_users']['columns']['password']['config']['required'] = 0;
    $GLOBALS['TCA']['fe_users']['columns']['password']['config']['passwordPolicy'] = 'clubmanager';
    $GLOBALS['TCA']['fe_users']['columns']['password']['config']['fieldControl'] = [
        'passwordReset' => [
            'renderType' => 'PasswordReset',
        ]
    ];
    
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['passwordPolicies'] = [
        'clubmanager' => [
            'validators' => [
               
            ],
        ],
    ];
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', $fields);
  
});
