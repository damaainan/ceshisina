<?php

require __DIR__ . '/Public.php';

$strings = 'ABCDEFGHIJKLOMNOPQRSTUVWXYZ';

$time_start = microtime_float();
for ($i = 0; $i < TIMES; $i++) {
    print($strings);
}

$time_end = microtime_float();
$time1    = $time_end - $time_start;


$time_start = microtime_float();
for ($i = 0; $i < TIMES; $i++) {
    echo $strings;
}

$time_end = microtime_float();
$time2    = $time_end - $time_start;
echo "\n";
echo $time1;
echo "\n";
echo $time2;
echo "\n";

// 0.64798498153687
// 0.60389995574951