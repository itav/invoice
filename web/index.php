<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();
$app['debug'] = true;

require_once __DIR__ . '/../lib/routes.php';
require_once __DIR__ . '/../lib/services.php';

$app->run();