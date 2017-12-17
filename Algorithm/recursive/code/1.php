<?php

function factorial(int $n): int
{
    if ($n == 0) {
        return 1;
    }

    return $n * factorial($n - 1);
}


echo factorial(5);