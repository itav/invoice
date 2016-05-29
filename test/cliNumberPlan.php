<?php

require_once __DIR__ . '/../vendor/autoload.php';

$repo = new \App\NumberPlanRepo();

$np = $repo->find(1);

$date = new DateTime();
$number = 134;

$output = $np->prepare($number, $date);
echo $output;