<?php
// Routes

/** @var \Slim\App $app */

$app->get('/publish/{service}', \app\actions\PublishDataAction::class);

$app->post('/mattermost', \app\actions\MattermostAirqAction::class);
