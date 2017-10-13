<?php

require __DIR__ . '/Public.php';

function outputFilter(&$value) {
    if (is_string($value)) {
        $value = preg_replace('/\x{4eba}\x{0f72}\x{0f74}\x{0f84}\x{0f7c}/u', '', $value);
        $value = preg_replace('/\x{0963}|\x{0962}|\x{093a}/u', '', $value);
    }
}

function outputFilter_u(&$value) {
    $value = preg_replace('/\\\u0(f72|f74|f84|f7c|963|962|93a)/u', '', $value);
}

$output = array_fill(1, 1000 / 100, produceString());

$time_start = microtime_float();
for ($i = 0; $i < 1000; $i++) {
    array_walk_recursive($output, 'outputFilter');
    $json = json_encode($output);
}

$time_end = microtime_float();
$time     = $time_end - $time_start;
echo $time;
echo "\n";

$time_start = microtime_float();

for ($i = 0; $i < 1000; $i++) {
    $json = json_encode($output);
    outputFilter_u($json);
}

$time_end = microtime_float();
$time     = $time_end - $time_start;
echo $time;
echo "\n";

// 0.13700199127197
// 0.017148017883301