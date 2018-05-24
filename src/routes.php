<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

//$app->get('/[{name}]', function (Request $request, Response $response, array $args) {
//    // Sample log message
//    $this->logger->info("Slim-Skeleton '/' route");
//
//    // Render index view
//    return $this->renderer->render($response, 'index.phtml', $args);
//});

$app->get('/hello/{name}', function ($request, $response, $args) {
    return $response->write("Hello " . $args['name']);
});

$app->get('/mem', function ($request, $response, $args) {
    $this->memcached->set('key', 'value2');

    $res =  $this->memcached->get('key'); // Если всё ок, то выведет value

    return $response->write($res);
});
