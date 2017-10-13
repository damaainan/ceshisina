<?php

require __DIR__.'/Public.php';

$num = 1;

$time_start = microtime_float();
for ($i = 0; $i < TIMES; $i++) {
    $a = "ab{$num}cd";
}

$time_end = microtime_float();
$time     = $time_end - $time_start;
echo $time;
echo "\n";

$time_start = microtime_float();
for ($i = 0; $i < TIMES; $i++) {
    $a = 'ab' . $num . 'cd';
}

$time_end = microtime_float();
$time     = $time_end - $time_start;
echo $time;
echo "\n";

// 0.079208850860596
// 0.08406400680542