<?php
return [
	'ctrl' => [
		'title'	=> 'LLL:EXT:postmaster/Resources/Private/Language/locallang_db.xlf:tx_postmaster_domain_model_mailingstatistics',
		'label' => 'uid',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'dividers2tabs' => true,
		'hideTable' => true,

		'iconfile' => 'EXT:postmaster/Resources/Public/Icons/tx_postmaster_domain_model_mailingstatistics.gif'
	],
	'types' => [
		'1' => ['showitem' => ''],
	],
	'palettes' => [
		'1' => ['showitem' => ''],
	],
	'columns' => [

		'subject' => [
			'config' => [
                'type' => 'passthrough',
            ],
		],
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
        'total_recipients' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'total_sent' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'delivered' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'failed' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'deferred' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'bounced' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'tstamp_fav_sending' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'tstamp_real_sending' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'tstamp_finished_sending' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'queue_mail' => [
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_postmaster_domain_model_queuemail',
                'foreign_field' => 'mailing_statistics',
                'maxitems' => 1
            ],
        ],
        'queue_mail_uid' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
	],
];
