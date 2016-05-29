<?php

//$app = new Silex\Application();

$app->get('/list', 'App\\InvoiceController::listAction');
$app->get('/add', 'App\\InvoiceController::addAction');
$app->post('/add', 'App\\InvoiceController::saveAction')->bind('invoice_add');
$app->get('/info/{id}', 'App\\InvoiceController::infoAction')->assert('id', '\w+');
$app->get('/edit/{id}', 'App\\InvoiceController::addAction')->assert('id', '\w+');
$app->put('/edit/{id}', 'App\\InvoiceController::saveAction')->assert('id', '\w+');
$app->delete('/del/{id}', 'App\\InvoiceController::deleteAction')->assert('id', '\w+');
