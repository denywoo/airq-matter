<?php
// DIC configuration

$container = $app->getContainer();

// view renderer
$container['renderer'] = function (\Psr\Container\ContainerInterface $c) {
    $settings = $c->get('settings')['renderer'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

// monolog
$container['logger'] = function (\Psr\Container\ContainerInterface $c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
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