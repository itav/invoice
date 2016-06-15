<?php

//$app = new Silex\Application();

$app->match('/list', 'App\\InvoiceController::listAction')->method('GET|POST');;
$app->get('/add', 'App\\InvoiceController::addAction');
$app->get('/print/{id}', 'App\\InvoiceController::printAction')->assert('id', '\w+');
$app->get('/print/pdf/{id}', 'App\\InvoiceController::printPdfAction')->assert('id', '\w+');
$app->post('/add', 'App\\InvoiceController::saveAction')->bind('invoice_add');
$app->get('/info/{id}', 'App\\InvoiceController::infoAction')->assert('id', '\w+');
$app->get('/edit/{id}', 'App\\InvoiceController::addAction')->assert('id', '\w+');
$app->post('/edit/{id}', 'App\\InvoiceController::saveAction')->assert('id', '\w+');
$app->get('/del/{id}', 'App\\InvoiceController::deleteAction')->assert('id', '\w+');


$app->match('/sub/list', 'App\\SubscriptionController::listAction')->method('GET|POST');
$app->get('/sub/add', 'App\\SubscriptionController::addAction');
$app->post('/sub/add', 'App\\SubscriptionController::saveAction')->bind('sub_add');
$app->get('/sub/info/{id}', 'App\\SubscriptionController::infoAction')->assert('id', '\w+');
$app->get('/sub/edit/{id}', 'App\\SubscriptionController::addAction')->assert('id', '\w+');
$app->post('/sub/edit', 'App\\SubscriptionController::saveAction')->assert('id', '\w+');
$app->get('/sub/del/{id}', 'App\\SubscriptionController::deleteAction')->assert('id', '\w+');

$app->post('/cron/gen/{id}', 'App\\CronController::generateInvoice')->assert('id', '\w+');