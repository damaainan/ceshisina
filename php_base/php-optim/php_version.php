<?php

require __DIR__ . '/Public.php';

$time_start = microtime_float();
for ($i = 0; $i < TIMES; $i++) {
    $a = phpversion();
}

$time_end = microtime_float();
$time     = $time_end - $time_start;
echo $time;
echo "\n";

$time_start = microtime_float();
for ($i = 0; $i < TIMES; $i++) {
    $a = PHP_VERSION;
}

$time_end = microtime_float();
$time     = $time_end - $time_start;
echo $time;
echo "\n";

// 0.20830798149109
// 0.054555892944336