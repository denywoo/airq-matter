<?php

// Routes

/** @var \Slim\App $app */

$app->get('/publish/{service}', \app\actions\PublishDataAction::class)->add($app->getContainer()->get('auth'));

$app->post('/mattermost', \app\actions\MattermostAirqAction::class)->add($app->getContainer()->get('auth'));
