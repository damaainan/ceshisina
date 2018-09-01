<?php
function insertionSort(array $numbers = array())
{
    $count = count($numbers);
    if ($count <= 1) {
        return $numbers;
    }

    for ($i = 1; $i < $count; $i++) {
        $temp = $numbers[$i];
        for ($j = $i - 1; $j >= 0 && $numbers[$j] > $temp; $j--) {
            $numbers[$j + 1] = $numbers[$j];
        }
        $numbers[$j + 1] = $temp;
    }

    return $numbers;
}

$arr = [];
for ($i = 0; $i < 5000; $i++) {
    $arr[] = rand(1, 10000);
}

$start_time = microtime(true);

$sort = insertionSort($arr);

$end_time = microtime(true);
$need_time = $end_time - $start_time;

print_r("排序耗时:" . $need_time . "\r\n");