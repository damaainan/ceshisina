<?php

require __DIR__.'/Public.php';

$time_start = microtime_float();
for ($i = 0; $i < TIMES; $i++) {
    $a = time();
}

$time_end = microtime_float();
$time     = $time_end - $time_start;
echo $time;
echo "\n";

$time_start = microtime_float();
for ($i = 0; $i < TIMES; $i++) {
    $a = $_SERVER['REQUEST_TIME'];
}

$time_end = microtime_float();
$time     = $time_end - $time_start;
echo $time;
echo "\n";

// 0.28743004798889
// 0.085227966308594