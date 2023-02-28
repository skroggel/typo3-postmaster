<?php
return [
	'ctrl' => [
		'title'	=> 'LLL:EXT:postmaster/Resources/Private/Language/locallang_db.xlf:tx_postmaster_domain_model_bouncemail',
		'label' => 'uid',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'dividers2tabs' => true,
		'hideTable' => true,

		'searchFields' => 'hash, url',
		'iconfile' => 'EXT:postmaster/Resources/Public/Icons/tx_postmaster_domain_model_bouncemail.gif'
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

        'status' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],

		'type' => [
            'config' => [
                'type' => 'passthrough',
            ],
		],

        'email' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'subject' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'rule_number' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],

        'rule_category' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'header' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'body' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'header_full' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'body_full' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
	],
];
