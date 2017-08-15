<?php


function sum($a, $b, $c) 
{
    return $a + $b + $c;
}

function partial($funcName, ...$args)
{
    return function (...$innerArgs) use ($funcName, $args) {
        $allArgs = array_merge($args, $innerArgs);
        return call_user_func_array($funcName, $allArgs);
    };
}

$sum = partial("sum", 10, 20);
$sum = $sum(30);
echo $sum;
