<?php
$EM_CONF[$_EXTKEY] = [
	'title' => 'Clubmanager Base',
	'description' => 'Clubmanager Base - The free TYPO3 module for managing clubs, foundations and associations. The only thing you need are members!',
	'category' => 'misc',
	'author' => 'wirkwerk.com und codemacher.de',
	'author_email' => 'post@quicko.software',
	'author_company' => 'Quicko - Der Clubmanager',
	'state' => 'stable',
	'clearCacheOnLoad' => 1,
	'version' => '1.0.0',
	'constraints' => [
		'depends' => [
			'typo3' => '11.5.0-11.5.99',
			'static_info_tables' => '11.5.0-11.5.99'
		],
		'conflicts' => [
		],
		'suggests' => [
		],
	]
];
