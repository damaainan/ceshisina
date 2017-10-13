<?php

require __DIR__ . '/Public.php';

$n = null;

$time_start = microtime_float();
for ($i = 0; $i < TIMES; $i++) {
    if (is_null($n)) {}
}

$time_end = microtime_float();
$time     = $time_end - $time_start;
echo $time;
echo "\n";

$time_start = microtime_float();
for ($i = 0; $i < TIMES; $i++) {
    if (null === $n) {}
}

$time_end = microtime_float();
$time     = $time_end - $time_start;
echo $time;
echo "\n";

// 0.058942079544067
// 0.054154872894287