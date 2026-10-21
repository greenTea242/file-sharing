<?php

declare(strict_types=1);

use Pimple\Container;

return function (Container $c) {
    $c['settings'] = [
        'db' => [
            'driver' => 'pgsql',
            'host' => 'localhost',
            'port' => 5432,
            'username' => 'admin',
            'name' => 'file_sharing',
            'password' => 'password',
        ],
        'twig' => [            
            'cache' => __DIR__ . '/../resources/templates/cache'
        ],
        'displayErrorDetails' => true,
        // 'phpNativeDownloader' | 'xSendFileDownloader'
        'fileDownloaderType' => 'phpNativeDownloader',
        'redis' => [
            'scheme' => 'redis',
            'host' => '127.0.0.1',
            'port' => '6379',
            'auth' => 'password',
            'database' => 0,
            'max_attempts' => 0,
            'retry_seconds' => 5
        ],
        'workers' => [            
            'videoPreviewWorker' => false,
            'thumbnailWorker' => false
        ]
    ];
};
