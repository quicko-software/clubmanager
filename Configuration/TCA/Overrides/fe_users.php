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
                'type' => 'input',
                'default' => 0,
                'eval' => 'datetime,int',
                'renderType' => 'inputDateTime',
            ]
        ]
      
    ];

    $GLOBALS['TCA']['fe_users']['columns']['password']['config']['required'] = 0;
    
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', $fields);
  
});
