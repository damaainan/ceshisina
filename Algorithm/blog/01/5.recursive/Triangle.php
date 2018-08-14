<?php

// 三角函数

function getTriangleNumber($num) {
    $total = 0;

    while ($num > 0) {
        $total = $total + $num;
        $num--;
    }
    return $total;
}

//echo getTriangleNumber(10);

function getTriangleNumberByRecurive($num) {
    if ($num == 1) {
        return 1;
    }

    return $num + getTriangleNumberByRecurive($num - 1);
}

echo getTriangleNumberByRecurive(11);



