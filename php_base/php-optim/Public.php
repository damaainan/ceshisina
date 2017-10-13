<?php

define('TIMES', 100000);

function microtime_float() {
    list($usec, $sec) = explode(" ", microtime());

    return ((float)$usec + (float)$sec);
}

function produceString($length = 8, $notO0 = false) {
    $strings = 'ABCDEFGHIJKLOMNOPQRSTUVWXYZ';
    $numbers = '1234567890';
    if ($notO0) {
        $strings = str_replace('O', '', $strings);
        $numbers = str_replace('0', '', $numbers);
    }
    $pattern = $strings . $numbers;
    $max     = strlen($pattern) - 1;
    $key     = '';
    for ($i = 0; $i < $length; $i++) {
        $key .= $pattern{mt_rand(0, $max)};
    }

    return $key;
}

