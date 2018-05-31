<?php
return [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/../templates/',
        ],

        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
            'level' => \Monolog\Logger::DEBUG,
        ],

        // Memcached settings
        'memcached' => [
            'host' => 'localhost',
            'port' => '11211',
        ],

        // Auth tokens
        'auth_tokens' => [
            'AirQ' => 'b7EiGSTDJbeu4JiGc3NYPEfn',
            'Mattermost' => 'wlXZTug8PsU40XXkTXZ8T9MU',
        ],
    ],
];
