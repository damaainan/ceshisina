<?php

require __DIR__.'/Public.php';

$array = array_fill(1, TIMES, produceString());

$time_start = microtime_float();
for ($i = 0; $i < sizeof($array); $i++) {}

$time_end = microtime_float();
$time     = $time_end - $time_start;
echo $time;
echo "\n";

$count      = sizeof($array);
$time_start = microtime_float();
for ($i = 0; $i < $count; $i++) {}
$time_end = microtime_float();
$time     = $time_end - $time_start;
echo $time;
echo "\n";

// 0.15870404243469
// 0.022637128829956