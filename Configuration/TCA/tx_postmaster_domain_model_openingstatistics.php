<?php
return [
	'ctrl' => [
		'title'	=> 'LLL:EXT:postmaster/Resources/Private/Language/locallang_db.xlf:tx_postmaster_domain_model_openingstatistics',
		'label' => 'uid',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'dividers2tabs' => true,
		'hideTable' => true,

		'searchFields' => 'hash',
		'iconfile' => 'EXT:postmaster/Resources/Public/Icons/tx_postmaster_domain_model_openingstatistics.gif'
	],
	'interface' => [
		'showRecordFieldList' => '',
	],
	'types' => [
		'1' => ['showitem' => ''],
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
        'counter' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'queue_mail' => [
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_postmaster_domain_model_queuemail',
                'maxitems' => 1
            ],
        ],
        'queue_mail_uid' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'queue_recipient' => [
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_postmaster_domain_model_queuerecipient',
                'maxitems' => 1
            ],
        ]
	],
];
