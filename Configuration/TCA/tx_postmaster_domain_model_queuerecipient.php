<?php
return [
	'ctrl' => [
		'title'	=> 'LLL:EXT:postmaster/Resources/Private/Language/locallang_db.xlf:tx_postmaster_domain_model_queuerecipient',
		'label' => 'uid',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => true,
		'hideTable' => true,

		'searchFields' => 'email, first_name, last_name, subject, status',
		'iconfile' => 'EXT:postmaster/Resources/Public/Icons/tx_postmaster_domain_model_queuerecipient.gif'
	],
    'types' => [
        '1' => ['showitem' => ''],
    ],
    'palettes' => [
        '1' => ['showitem' => ''],
    ],
	'columns' => [

        'queue_mail' => [
            'config' => [
                'type' => 'passthrough',
                'foreign_table' => 'tx_postmaster_domain_model_queuemail',
            ],
        ],

		'email' => [
            'config' => [
                'type' => 'passthrough',
            ],
		],
        'title' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'salutation' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
		'first_name' => [
            'config' => [
                'type' => 'passthrough',
            ],
		],
		'last_name' => [
            'config' => [
                'type' => 'passthrough',
            ],
		],
        'subject' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'marker' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'status' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'language_code' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
	],
];
