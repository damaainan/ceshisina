<?php
function swap(&$x, &$y)
{
    $t = $x;
    $x = $y;
    $y = $t;
}

function max_heapify(&$arr, $start, $end)
{
    // 建立父节点指标和子节点指标
    $dad = $start;
    $son = $dad * 2 + 1;
    if ($son >= $end) //若子节点指标超过范围直接跳出函数
    {
        return;
    }

    // 先比较两个子节点大小，选择最大的
    if ($son + 1 < $end && $arr[$son] < $arr[$son + 1]) {
        $son++;
    }

    // 如果父节点小于子节点时，交换父子内容再继续子节点和孙节点比较
    if ($arr[$dad] <= $arr[$son]) {
        swap($arr[$dad], $arr[$son]);
        max_heapify($arr, $son, $end);
    }
}

function heap_sort($arr)
{
    $len = count($arr);

    //初始化，i从最后一个父节点开始调整
    for ($i = $len / 2 - 1; $i >= 0; $i--) {
        max_heapify($arr, $i, $len);
    }

    //先将第一个元素和已排好元素前一位做交换，再从新调整，直到排序完毕
    for ($i = $len - 1; $i > 0; $i--) {
        swap($arr[0], $arr[$i]);
        max_heapify($arr, 0, $i);
    }
    return $arr;
}

$arr = [];
for ($i = 0; $i < 5000; $i++) {
    $arr[] = rand(1, 10000);
}

$start_time = microtime(true);

$sort = heap_sort($arr);

$end_time = microtime(true);
$need_time = $end_time - $start_time;

print_r("排序耗时:" . $need_time . "\r\n");