<?php

require_once __DIR__ . '/../vendor/autoload.php';

$request = \Symfony\Component\HttpFoundation\Request::create(
    '/cron/gen/all',
    'POST',
    [
        'from' => '2016-06-01',
        'to' => '2016-06-30'
    ]
);
$request->overrideGlobals();

$app = new Silex\Application();
require_once __DIR__ . '/../lib/routes.php';
require_once __DIR__ . '/../lib/services.php';

$app->run();