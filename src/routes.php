<?php
// Routes

/** @var \Slim\App $app */

$app->get('/publish/{service}', \app\actions\PublishDataAction::class);

$app->post('/mattermost', \app\actions\MattermostAirqAction::class);

$app->post('/mongo', function (\Slim\Http\Request $request, \Slim\Http\Response $response, $args){
    $date = new DateTime();
    $date->setTimezone(new DateTimeZone('UTC'));
    list($hours, $minutes) = explode(':', $date->format('H:i'));
    $hours = intval($hours);
    $minutes = round(intval($minutes) / 15) * 15;
    $date->setTime($hours, $minutes, 0);
    $timestamp = $date->getTimestamp();
    $utcDateTime = new \MongoDB\BSON\UTCDateTime($timestamp * 1000);
    var_dump($date);

    /** @var \MongoDB\Client $mongoClient */
    $mongoClient = $this->get('mongo');

    $collection = $mongoClient->AirQ->datalog;
    $result = $collection->insertOne([
        'datetime' => $utcDateTime,
        'temperature' => '23.5',
        'humidity' => '44.7',
        'CO2' => '689',
    ]);

    printf("Inserted %d document(s)\n", $result->getInsertedCount());

    var_dump($result->getInsertedId());
});