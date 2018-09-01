<?php

function selectSort(array $numbers = array())
{
    $count = count($numbers);
    if ($count <= 1) {
        return $numbers;
    }
    for ($i = 0; $i < $count - 1; $i++) {
        $min = $i;
        for ($j = $i + 1; $j < $count; $j++) {
            if ($numbers[$min] > $numbers[$j]) {
                $min = $j;
            }
        }

        if ($min != $i) {
            $temp          = $numbers[$min];
            $numbers[$min] = $numbers[$i];
            $numbers[$i]   = $temp;
        }
    }
    return $numbers;
}

$arr = [];
for ($i = 0; $i < 5000; $i++) {
    $arr[] = rand(1, 10000);
}

$start_time = microtime(true);

$sort = selectSort($arr);

$end_time = microtime(true);
$need_time = $end_time - $start_time;

print_r("排序耗时:" . $need_time . "\r\n");