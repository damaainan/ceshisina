<?php

require __DIR__.'/Public.php';

$a = range(1, 1000);
$i = 0;
$time_start = microtime_float();
while (++$i < TIMES) {
    if (isset($a)) {
        $b = $a;
    } else {
        $b = null;
    }
}
$time_end = microtime_float();
$time     = $time_end - $time_start;
echo $time;
echo "\n";

$i          = 0;
$time_start = microtime_float();

while (++$i < TIMES) {
    $b = isset($a) ? $a : null;
}

$time_end = microtime_float();
$time     = $time_end - $time_start;
echo $time;
echo "\n";

// 0.048048973083496
// 0.043617010116577