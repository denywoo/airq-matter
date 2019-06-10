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
        'timezone' => getenv('TIMEZONE'),

        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => isset($_ENV['docker']) ? 'php://stdout' : "{$appDir}/logs/app.log",
            'level' => ENV_DEV ? \Monolog\Logger::DEBUG : \Monolog\Logger::CRITICAL,
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

        // Registered data settings
        'registered_data' => [
            'AirQ/Climate/Temperature' => [
                'title' => 'Temperature',
                'unit' => '°C',
                'feel' => [
                    23 => ':snowflake:', // cold
                    25 => ':sunglasses:', // comfort
                    29 => ':sweat:', // it is hot
                    100 => ':fire:', // too hot
                ],
                'onGraph' => true,
            ],
            'AirQ/Climate/Humidity' => [
                'title' => 'Humidity',
                'unit' => '%',
                'feel' => [
                    30 => ':zap:', // dry
                    40 => ':ok_hand:', // not so bad if you in Moscow
                    60 => ':sunglasses:', // comfort if you are human
                    85 => ':palm_tree:', // comfort if you are plant
                    146 => ':umbrella:', // precipitation is possible
                ],
                'onGraph' => true,
            ],
            'AirQ/CO2/PPM' => [
                'title' => 'CO₂',
                'unit' => 'ppm',
                'feel' => [
                    500 => ':deciduous_tree:', // oh, fresh air!
                    700 => ':grinning:', // good
                    1000 => ':confused:', // not so good
                    1500 => ':disappointed:', // bad
                    5555 => ':finnadie:', // you are dead!
                ],
                'onGraph' => true,
            ],
        ],
    ],
];
