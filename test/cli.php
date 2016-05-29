<?php

require_once __DIR__ . '/../vendor/autoload.php';

$request = \Symfony\Component\HttpFoundation\Request::create(
    '/add',
    'POST'
);
$request->overrideGlobals();

$app = new Silex\Application();
require_once __DIR__ . '/../lib/routes.php';
require_once __DIR__ . '/../lib/services.php';

$app->run();