<?php

return [
    'redis_cluster' => [
        'host' => env('REDIS_CLUSTER_HOST', '127.0.0.1'),
        'port' => env('REDIS_CLUSTER_PORT', '6379'),
        'prefix' => env('REDIS_CLUSTER_PREFIX', ''),
    ],
];
