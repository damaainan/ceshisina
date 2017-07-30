<?php

function swap(array &$arr, $a, $b)
{
    $temp    = $arr[$a];
    $arr[$a] = $arr[$b];
    $arr[$b] = $temp;
}

function Partition(array &$arr, $low, $high)
{
    $pivot = $arr[$low]; //选取子数组第一个元素作为枢轴
    while ($low < $high) {
        //从数组的两端交替向中间扫描
        while ($low < $high && $arr[$high] >= $pivot) {
            $high--;
        }
        swap($arr, $low, $high); //终于遇到一个比$pivot小的数，将其放到数组低端
        while ($low < $high && $arr[$low] <= $pivot) {
            $low++;
        }
        swap($arr, $low, $high); //终于遇到一个比$pivot大的数，将其放到数组高端
    }
    return $low; //返回high也行，毕竟最后low和high都是停留在pivot下标处
}

function QSort(array &$arr, $low, $high)
{
    if ($low < $high) {
        $pivot = Partition($arr, $low, $high); //将$arr[$low...$high]一分为二，算出枢轴值
        QSort($arr, $low, $pivot - 1); //对低子表进行递归排序
        QSort($arr, $pivot + 1, $high); //对高子表进行递归排序
    }
}

function QuickSort(array &$arr)
{
    $low  = 0;
    $high = count($arr) - 1;
    QSort($arr, $low, $high);
}


$arr = array(9,1,5,8,3,7,4,6,2);
QuickSort($arr);
var_dump($arr);