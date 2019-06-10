<?php
// DIC configuration

$container = $app->getContainer();

// monolog
$container['logger'] = function (\Psr\Container\ContainerInterface $c) {
    $settings = $c->get('settings');
    Monolog\Logger::setTimezone(new DateTimeZone($settings['timezone']));
    $logger = new Monolog\Logger($settings['logger']['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['logger']['path'], $settings['logger']['level']));
    return $logger;
};

// memcached
$container['memcached'] = function (\Psr\Container\ContainerInterface $c) {
    $settings = $c->get('settings')['memcached'];
    $memcached = new Memcached();
    $memcached->addServer($settings['host'], $settings['port']);
    return $memcached;
};

// token auth
$container['auth'] = function (\Psr\Container\ContainerInterface $c) {
    $tokens = $c->get('settings')['auth_tokens'];
    $logger = $c->has('logger') ? $c->get('logger') : null;
    $authMiddleware = new app\middlewares\TokenAuthMiddleware($tokens, $logger);
    return $authMiddleware;
};

// mongodb
$container['mongo'] = function (\Psr\Container\ContainerInterface $c) {
    $settings = $c->get('settings')['mongodb'];
    $mongoDsn = "mongodb://{$settings['host']}:{$settings['port']}";
    $client = new \MongoDB\Client($mongoDsn);
    return $client;
};

// timezone
$container['timezone'] = function (\Psr\Container\ContainerInterface $c) {
    $timeZone = new \DateTimeZone($c->get('settings')['timezone']);
    return $timeZone;
};

// time rounded to 15 minutes
$container['time_quantum'] = function (\Psr\Container\ContainerInterface $c) {
    $date = new DateTime('now', $c->get('timezone'));
    list($hours, $minutes) = explode(':', $date->format('H:i'));
    $hours = intval($hours);
    $minutes = round(intval($minutes) / 15) * 15;
    $date->setTime($hours, $minutes, 0);
    return DateTimeImmutable::createFromMutable($date);
};