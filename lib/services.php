<?php

use Itav\Component\Serializer\Serializer;
use Symfony\Component\Templating\PhpEngine;
use Symfony\Component\Templating\TemplateNameParser;
use Symfony\Component\Templating\Loader\FilesystemLoader;
use Symfony\Component\Templating\Helper\SlotsHelper;

//$app = new Silex\Application();
require_once 'config.php';

$app['serializer'] = function() {
    return new Serializer();
};

$app['templating'] = function($app) {
    $loader = new FilesystemLoader($app['view_dirs']);
    $templating = new PhpEngine(new TemplateNameParser(), $loader);
    $templating->set(new SlotsHelper());
    return $templating;
};

$app['twig'] = function($app) {
    $loader = new Twig_Loader_Filesystem($app['twig_dirs']);
    $twig = new Twig_Environment($loader, [
        'cache' => __DIR__ . '/../app/cache/twig',
        'debug' => true,
    ]);
    return $twig;
};
