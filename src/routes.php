<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

$app->get('/publish/{service}', function (Request $request, Response $response, $args) {
    $queryParams = $request->getQueryParams();
//    $auth = $this->get('auth');
//    var_dump($auth);
    var_dump($args, $queryParams);
    return $response->write("Hello " . $args['name']);
})->add($app->getContainer()->get('auth'));

$app->get('/mem', function ($request, $response, $args) {
    $this->memcached->set('key', 'value2');

    $res =  $this->memcached->get('key'); // Если всё ок, то выведет value

    return $response->write($res);
});
