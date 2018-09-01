<?php
function shellSort(array $numbers = array())
{
    $count = count($numbers);
    if ($count <= 1) {
        return $numbers;
    }

    for ($gap = floor($count / 2); $gap > 0; $gap = floor($gap /= 2)) {
        for ($i = $gap; $i < $count; ++$i) {
            for ($j = $i - $gap; $j >= 0 && $numbers[$j + $gap] < $numbers[$j]; $j = $j - $gap) {
                // $temp               = $numbers[$j];
                // $numbers[$j]        = $numbers[$j + $gap];
                // $numbers[$j + $gap] = $temp;

                $numbers[$j] = [$numbers[$j + $gap], $numbers[$j + $gap] = $numbers[$j]][0]; // 快速交换变量
            }
        }
    }

    return $numbers;
}

$arr = [];
for ($i = 0; $i < 5000; $i++) {
    $arr[] = rand(1, 10000);
}

$start_time = microtime(true);

$sort = shellSort($arr);

$end_time = microtime(true);
$need_time = $end_time - $start_time;

print_r("排序耗时:" . $need_time . "\r\n");