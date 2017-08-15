<?php

function currySum($a)
{
    return function ($b) use ($a) {
        return function ($c) use ($a, $b) {
            return $a + $b + $c;
        };
    };
}
$sum = currySum(10)(20)(30);
echo $sum;
