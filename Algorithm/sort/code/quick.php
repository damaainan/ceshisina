<?php
function quickSort(array $numbers = array())
{
    $count = count($numbers);
    if ($count <= 1) {
        return $numbers;
    }

    $left      = $right      = array();
    $mid_value = $numbers[0];

    for ($i = 1; $i < $count; $i++) {
        if ($numbers[$i] < $mid_value) {
            $left[] = $numbers[$i];
        } else {
            $right[] = $numbers[$i];
        }

    }
    return array_merge(quickSort($left), (array) $mid_value, quickSort($right));
}

$arr = [];
for ($i = 0; $i < 5000; $i++) {
    $arr[] = rand(1, 10000);
}

$start_time = microtime(true);

$sort = quickSort($arr);

$end_time = microtime(true);
$need_time = $end_time - $start_time;

print_r("排序耗时:" . $need_time . "\r\n");