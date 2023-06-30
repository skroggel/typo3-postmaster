<?php

/***************************************************************
 * Extension Manager/Repository config file for ext: "postmaster"
 *
 * Auto generated by Extension Builder 2014-11-07
 *
 * Manual updates:
 * Only the data in the array - anything else is removed by next write.
 * "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = [
	'title' => 'Postmaster',
	'description' => 'Extension sending e-mails and bulk-mailings',
	'category' => 'plugin',
	'author' => 'Maximilian Fäßler, Steffen Kroggel',
	'author_email' => 'maximilian@faesslerweb.de, developer@steffenkroggel.de',
	'state' => 'stable',
	'internal' => '',
	'uploadfolder' => '0',
	'createDirs' => '',
	'clearCacheOnLoad' => 0,
	'version' => '9.5.9',
	'constraints' => [
		'depends' => [
            'typo3' => '9.5.0-9.5.99',
            'accelerator' => '9.5.2-9.5.99',
            'core_extended' => '9.5.3-9.5.99'
		],
		'conflicts' => [
		],
		'suggests' => [
		],
	],
];
