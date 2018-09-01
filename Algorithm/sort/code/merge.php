<?php
function mergeSort(array $numbers = array())
{
    $count = count($numbers);
    if ($count <= 1) {
        return $numbers;
    }

    // 将数组分成两份 $half = ceil( $count / 2 );
    $half  = ($count >> 1) + ($count & 1);
    $arr2d = array_chunk($numbers, $half);

    $left  = mergeSort($arr2d[0]);
    $right = mergeSort($arr2d[1]);

    while (count($left) && count($right)) {
        if ($left[0] < $right[0]) {
            $reg[] = array_shift($left);
        } else {
            $reg[] = array_shift($right);
        }

    }
    return array_merge($reg, $left, $right);
}

$arr = [];
for ($i = 0; $i < 5000; $i++) {
    $arr[] = rand(1, 10000);
}

$start_time = microtime(true);

$sort = mergeSort($arr);

$end_time = microtime(true);
$need_time = $end_time - $start_time;

print_r("排序耗时:" . $need_time . "\r\n");