<?php

require __DIR__ . '/Public.php';

$array = array_fill(1, TIMES, produceString());

$time_start = microtime_float();
for ($i = 0; $i < TIMES; $i++) {
    if (in_array('1', $array)) {}
}

$time_end = microtime_float();
$time     = $time_end - $time_start;

echo $time;
echo "\n";

$time_start = microtime_float();
for ($i = 0; $i < TIMES; $i++) {
    if (isset($array['1'])) {}
}

$time_end = microtime_float();
$time     = $time_end - $time_start;
echo $time;
echo "\n";

// 37.344570875168
// 0.045185089111328