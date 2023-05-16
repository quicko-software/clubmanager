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
	'version' => '1.0.5',
	'constraints' => [
		'depends' => [
			'typo3' => '11.5.0-11.5.99',
			'static_info_tables' => '11.5.0-11.5.99'
		],
		'suggests' => [
            'vhs' => '6.1.0-6.1.99',
            'felogin' => '11.5.0-11.5.99',
            'bootstrap_package' => '12.0.0-12.0.99',
            'cookieman' => '2.14.0-2.14.99'
		],
        'conflicts' => [
        ],
	]
];
