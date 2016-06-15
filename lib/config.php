<?php


$app['view_dirs'] = [
    __DIR__ . '/../src/views/%name%',
    __DIR__ . '/../vendor/itav/form/src/views/php/%name%',
    __DIR__ . '/../vendor/itav/table/src/views/php/%name%',
];

$app['twig_dirs'] = [
    __DIR__ . '/../src/views',
    __DIR__ . '/../vendor/itav/form/src/views/twig',
    __DIR__ . '/../vendor/itav/table/src/views/twig',
];