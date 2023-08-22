<?php

return [
    'default_collector' => env('LARAVEL_DOCGEN_COLLECTOR', 'db'),
    'default_deployer' => env('LARAVEL_DOCGEN_DEPLOYER', 'stdout'),
    'template_paths' => (array) env('LARAVEL_DOCGEN_TEMPLATE_PATH', resource_path('docgen')),
    'collectors' => [
        'db' => [
            'class' => Libry\LaravelDocgen\Collector\Db\DbCollector::class,
            'connection' => env('DB_CONNECTION', 'mysql'),
        ],
    ],
    'deployers' => [
        'stdout' => [
            'class' => Libry\LaravelDocgen\Deployer\StandardOutputDeployer::class,
        ],
        'local' => [
            'class' => Libry\LaravelDocgen\Deployer\FilesystemDeployer::class,
            'disk' => 'local',
        ],
        's3' => [
            'class' => Libry\LaravelDocgen\Deployer\FilesystemDeployer::class,
            'disk' => 's3',
        ],
        'esa' => [
            'class' => Libry\LaravelDocgen\Deployer\EsaDeployer::class,
            'client_id' => env('LARAVEL_DOCGEN_ESA_CLIENT_ID'),
            'client_secret' => env('LARAVEL_DOCGEN_ESA_CLIENT_SECRET'),
            'team_name' => env('LARAVEL_DOCGEN_ESA_TEAM_NAME'),
        ],
    ],
];
