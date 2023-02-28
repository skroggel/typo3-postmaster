<?php
return [
	'ctrl' => [
		'title'	=> 'LLL:EXT:postmaster/Resources/Private/Language/locallang_db.xlf:tx_postmaster_domain_model_link',
		'label' => 'uid',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'dividers2tabs' => true,
		'hideTable' => true,

		'searchFields' => 'hash, url',
		'iconfile' => 'EXT:postmaster/Resources/Public/Icons/tx_postmaster_domain_model_link.gif'
	],
	'interface' => [
		'showRecordFieldList' => 'hash, url',
	],
	'types' => [
		'1' => ['showitem' => 'hash, url'],
	],
	'palettes' => [
		'1' => ['showitem' => ''],
	],
	'columns' => [

		'hash' => [
            'config' => [
                'type' => 'passthrough',
            ],
		],
		'url' => [
			'config' => [
                'type' => 'passthrough',
            ],
		],

        'queue_mail' => [
            'config' => [
                'type' => 'passthrough',
                'foreign_table' => 'tx_postmaster_domain_model_queuemail',
            ],
        ],
	],
];
