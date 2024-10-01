<?php

use Libry\LaravelDocgen\Collector\Db\DbCollector;
use Libry\LaravelDocgen\Deployer\EsaDeployer;
use Libry\LaravelDocgen\Deployer\FilesystemDeployer;
use Libry\LaravelDocgen\Deployer\StandardOutputDeployer;

return [
    'default_collector' => env('LARAVEL_DOCGEN_COLLECTOR', 'db'),
    'default_deployer' => env('LARAVEL_DOCGEN_DEPLOYER', 'stdout'),
    'template_paths' => (array) env('LARAVEL_DOCGEN_TEMPLATE_PATH', resource_path('docgen')),
    'collectors' => [
        'db' => [
            'class' => DbCollector::class,
            'connection' => env('DB_CONNECTION', 'mysql'),
        ],
    ],
    'deployers' => [
        'stdout' => [
            'class' => StandardOutputDeployer::class,
        ],
        'local' => [
            'class' => FilesystemDeployer::class,
            'disk' => 'local',
        ],
        's3' => [
            'class' => FilesystemDeployer::class,
            'disk' => 's3',
        ],
        'esa' => [
            'class' => EsaDeployer::class,
            'client_id' => env('LARAVEL_DOCGEN_ESA_CLIENT_ID'),
            'client_secret' => env('LARAVEL_DOCGEN_ESA_CLIENT_SECRET'),
            'team_name' => env('LARAVEL_DOCGEN_ESA_TEAM_NAME'),
        ],
    ],
];
