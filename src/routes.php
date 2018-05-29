<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

/** @var \Slim\App $app */

$app->get('/publish/{service}', function (Request $request, Response $response, $args) {
    $queryParams = $request->getQueryParams();
    $key = "{$args['service']}/{$queryParams['task']}/{$queryParams['valuename']}";
    $value = $queryParams['value'];

    if (empty($key)) {
        return $response->withStatus(400);
    }

    $this->memcached->set($key, $value);
    $this->logger->info('Value added', ['key' => $key, 'value' => $value]);
})->add($app->getContainer()->get('auth'));

$app->get('/mem', function (Request $request, Response $response, $args) {
    $this->memcached->set('key', 'value2');

    $res =  $this->memcached->get('key'); // Если всё ок, то выведет value

    return $response->write($res);
});
