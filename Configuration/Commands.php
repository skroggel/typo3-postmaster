<?php

return [
    'postmaster:send' => [
        'class' => \Madj2k\Postmaster\Command\SendCommand::class,
        'schedulable' => true,
    ],
    'postmaster:analyseStatistics' => [
        'class' => \Madj2k\Postmaster\Command\AnalyseStatisticsCommand::class,
        'schedulable' => true,
    ],
    'postmaster:analyseBounceMails' => [
        'class' => \Madj2k\Postmaster\Command\AnalyseBounceMailsCommand::class,
        'schedulable' => true,
    ],
    'postmaster:processBounceMails' => [
        'class' => \Madj2k\Postmaster\Command\ProcessBounceMailsCommand::class,
        'schedulable' => true,
    ],
    'postmaster:cleanup' => [
        'class' => \Madj2k\Postmaster\Command\CleanupCommand::class,
        'schedulable' => true,
    ],
];
