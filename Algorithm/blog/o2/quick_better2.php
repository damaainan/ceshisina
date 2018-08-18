<?php
function swap(array &$arr, $a, $b)
{
    $temp    = $arr[$a];
    $arr[$a] = $arr[$b];
    $arr[$b] = $temp;
}

function InsertSort(array &$arr)
{
    $count = count($arr);
    //数组中第一个元素作为一个已经存在的有序表
    for ($i = 1; $i < $count; $i++) {
        $temp = $arr[$i]; //设置哨兵
        for ($j = $i - 1; $j >= 0 && $arr[$j] > $temp; $j--) {  // 和 temp 的比较，如果小于等于则终止循环
            $arr[$j + 1] = $arr[$j]; //记录后移
        }
        $arr[$j + 1] = $temp; //插入到正确的位置
    }
}

/// 优化二：优化不必要的交换
function Partition(array &$arr, $low, $high)
{
    $mid = floor($low + ($high - $low) / 2); //计算数组中间的元素的下标
    if ($arr[$low] > $arr[$high]) {
        swap($arr, $low, $high);
    }
    if ($arr[$mid] > $arr[$high]) {
        swap($arr, $mid, $high);
    }
    if ($arr[$low] < $arr[$mid]) {
        swap($arr, $low, $mid);
    }

    //经过上面三步之后，$arr[$low]已经成为整个序列左中右端三个关键字的中间值
    $pivot = $arr[$low];

    $temp = $pivot;

    while ($low < $high) {
        //从数组的两端交替向中间扫描（当 $low 和 $high 碰头时结束循环）
        while ($low < $high && $arr[$high] >= $pivot) {
            $high--;
        }
        //swap($arr,$low,$high);    //终于遇到一个比$pivot小的数，将其放到数组低端
        $arr[$low] = $arr[$high]; //使用替换而不是交换的方式进行操作

        while ($low < $high && $arr[$low] <= $pivot) {
            $low++;
        }
        //swap($arr,$low,$high);    //终于遇到一个比$pivot大的数，将其放到数组高端
        $arr[$high] = $arr[$low];
    }

    $arr[$low] = $temp; //将枢轴数值替换回 $arr[$low];

    return $low; //返回high也行，毕竟最后low和high都是停留在pivot下标处
}

// 优化四：优化递归操作
//规定数组长度阀值
define('MAX_LENGTH_INSERT_SORT', 7);

function QSort(array &$arr, $low, $high)
{
    //当 $low >= $high 时表示不能再进行分组，已经能够得出正确结果了
    if (($high - $low) > MAX_LENGTH_INSERT_SORT) {
        while ($low < $high) {
            $pivot = Partition($arr, $low, $high); //将$arr[$low...$high]一分为二，算出枢轴值
            QSort($arr, $low, $pivot - 1); //对低子表（$pivot左边的记录）进行递归排序
            $low = $pivot + 1;
        }
    } else {
        //直接插入排序
        InsertSort($arr);
    }
}

function QuickSort(array &$arr)
{
    $low  = 0;
    $high = count($arr) - 1;
    QSort($arr, $low, $high);
}

$arr = array(9, 1, 5, 8, 3, 7, 4, 6, 2);
QuickSort($arr);
var_dump($arr);
