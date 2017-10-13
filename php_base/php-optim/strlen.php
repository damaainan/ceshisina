<?php

require __DIR__.'/Public.php';

$strings = 'ABCDEFGHIJKLOMNOPQRSTUVWXYZ';

$time_start = microtime_float();
for ($i = 0; $i < TIMES; $i++) {
    if (strlen($strings) > 10) {}
}

$time_end = microtime_float();
$time     = $time_end - $time_start;
echo $time;
echo "\n";

$time_start = microtime_float();
for ($i = 0; $i < TIMES; $i++) {
    if (isset($strings[10])) {}
}

$time_end = microtime_float();
$time     = $time_end - $time_start;
echo $time;
echo "\n";

// 0.055904150009155
// 0.044722080230713