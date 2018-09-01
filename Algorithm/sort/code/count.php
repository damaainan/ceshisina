<?php
function countingSort(array $numbers = array())
{
    $count = count($numbers);
    if ($count <= 1) {
        return $numbers;
    }

    // 找出待排序的数组中最大值和最小值
    $min = min($numbers);
    $max = max($numbers);

    // 计算待排序的数组中每个元素的个数
    $count_array = array();
    for ($i = $min; $i <= $max; $i++) {
        $count_array[$i] = 0;
    }

    foreach ($numbers as $v) {
        $count_array[$v] = $count_array[$v] + 1;
    }

    $ret = array();
    foreach ($count_array as $k => $c) {
        for ($i = 0; $i < $c; $i++) {
            $ret[] = $k;
        }
    }
    return $ret;
}

$arr = [];
for ($i = 0; $i < 5000; $i++) {
    $arr[] = rand(1, 10000);
}

$start_time = microtime(true);

$sort = countingSort($arr);

$end_time = microtime(true);
$need_time = $end_time - $start_time;

print_r("排序耗时:" . $need_time . "\r\n");