<?php

require_once __DIR__ . '/../vendor/autoload.php';

$request = \Symfony\Component\HttpFoundation\Request::create(
    '/cron/gen/all',
    'POST',
    [
        'from' => '2000-02-28',
        'to' => '2036-05-31'
    ]
);
$request->overrideGlobals();

$app = new Silex\Application();
require_once __DIR__ . '/../lib/routes.php';
require_once __DIR__ . '/../lib/services.php';

$app->run();