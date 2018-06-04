<?php

$dotenv = new Dotenv\Dotenv(__DIR__ . '/..');
$dotenv->load();

define('ENV_DEV', getenv('ENV_DEV') == 1);

$appDir = __DIR__ . '/..';

return [
    'settings' => [
        // Application settings
        'displayErrorDetails' => ENV_DEV,
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        'baseUrl' => getenv('BASE_URL'),
        'appDir' => $appDir,

        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => isset($_ENV['docker']) ? 'php://stdout' : "{$appDir}/logs/app.log",
            'level' => ENV_DEV ? \Monolog\Logger::DEBUG : \Monolog\Logger::CRITICAL,
            'timezone' => getenv('TIMEZONE')
        ],

        // Memcached settings
        'memcached' => [
            'host' => getenv('MEMCACHED_HOST'),
            'port' => getenv('MEMCACHED_PORT'),
        ],

        // Mongodb
        'mongodb' => [
            'host' => getenv('MONGODB_HOST'),
            'port' => getenv('MONGODB_PORT'),
        ],

        // Auth tokens
        'auth_tokens' => [
            'AirQ' => getenv('PUBLICATION_TOKEN'),
            'Mattermost' => getenv('MATTERMOST_TOKEN'),
        ],

        // Mattermost
        'mattermost' => [
            'username' => 'AirQ', // Set to false if you want to use the default username
            'response_type' => 'ephemeral',
        ],
    ],
];
