<?php

function gcd(int $a, int $b): int
{
    if ($b == 0) {
        return $a;
    } else {
        return gcd($b, $a % $b);
    }
}

echo gcd(10, 3);
