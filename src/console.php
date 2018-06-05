<?php
$container = $app->getContainer();

$container['commands'] = [
    'LogAirqData' => app\console\LogAirqDataTask::class
];

$app->add(\adrianfalleiro\SlimCLIRunner::class);