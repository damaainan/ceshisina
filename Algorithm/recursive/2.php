<?php

function factorial(int $n): int
{
    $result = 1;
    for ($i = $n; $i > 0; $i--) {
        $result *= $i;
    }
    return $result;
}

echo factorial(5);
