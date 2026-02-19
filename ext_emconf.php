<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Clubmanager Base',
    'description' => 'The TYPO3 module for managing clubs, foundations and associations. With Clubmanager, you get the tool to manage the organization AND public relations of your club or association in a cost-saving and effective way.',
    'category' => 'misc',
    'author' => 'wirkwerk.com und codemacher.de',
    'author_email' => 'post@quicko.software',
    'author_company' => 'Quicko - The Clubmanager',
    'state' => 'stable',
    'clearCacheOnLoad' => 1,
    'version' => '2.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.0-13.4.99',
            'static_info_tables' => '13.4.0-13.4.99',
        ],
        'suggests' => [
            'php' => '8.3.1-8.3.99',
            'vhs' => '7.2.1-7.2.99',
            'cms-felogin' => '13.4.0-13.4.99',
            'bootstrap_package' => '16.0.0-16.0.99',
        ],
        'conflicts' => [
        ],
    ],
];
