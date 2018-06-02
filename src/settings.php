<?php

$dotenv = new Dotenv\Dotenv(__DIR__ . '/..');
$dotenv->load();

define('ENV_DEV', getenv('ENV_DEV') == 1);

return [
    'settings' => [
        'displayErrorDetails' => ENV_DEV,
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/../templates/',
        ],

        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
            'level' => ENV_DEV ? \Monolog\Logger::DEBUG : \Monolog\Logger::CRITICAL,
            'timezone' => getenv('TIMEZONE')
        ],

        // Memcached settings
        'memcached' => [
            'host' => getenv('MEMCACHED_HOST'),
            'port' => getenv('MEMCACHED_PORT'),
        ],

        // Auth tokens
        'auth_tokens' => [
            'AirQ' => getenv('PUBLICATION_TOKEN'),
            'Mattermost' => getenv('MATTERMOST_TOKEN'),
        ],
    ],
];
