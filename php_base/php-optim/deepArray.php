<?php

require __DIR__ . '/Public.php';

$time_start = microtime_float();
for ($i = 0; $i < TIMES; $i++) {
    $arr[1][2][3][4][5][6][7] = $i;
}

$time_end = microtime_float();
$time     = $time_end - $time_start;
echo $time;
echo "\n";

$time_start = microtime_float();
for ($i = 0; $i < TIMES; $i++) {
    $arr2[1] = $i;
}

$time_end = microtime_float();
$time     = $time_end - $time_start;
echo $time;
echo "\n";

// 0.10429906845093
// 0.051629066467285