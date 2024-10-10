<?php

return [
    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],
        // dummy
        's3' => [
            'driver' => 'local',
            'root' => storage_path('app/docgen'),
        ],
    ],
];
