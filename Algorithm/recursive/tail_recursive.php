<?php

function factorial1($n, $accumulator = 1)
{
    if ($n == 0) {
        return $accumulator;
    }

    return factorial1($n - 1, $accumulator * $n);
}

var_dump(factorial1(300));

function factorial($n, $accumulator = 1)
{
    if ($n == 0) {
        return $accumulator;
    }
    return function () use ($n, $accumulator) {
        return factorial($n - 1, $accumulator * $n);
    };
}

function trampoline($callback, $params)
{
    $result = call_user_func_array($callback, $params);
    while (is_callable($result)) {
        $result = $result();
    }
    return $result;
}
var_dump(trampoline('factorial', array(300)));
