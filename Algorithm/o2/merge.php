<?php

//交换函数
function swap(array &$arr, $a, $b)
{
    $temp    = $arr[$a];
    $arr[$a] = $arr[$b];
    $arr[$b] = $temp;
}

//归并算法总函数
function MergeSort(array &$arr)
{
    $start = 0;
    $end   = count($arr) - 1;
    MSort($arr, $start, $end);
}

function MSort(array &$arr, $start, $end)
{
    //当子序列长度为1时，$start == $end，不用再分组
    if ($start < $end) {
        $mid = floor(($start + $end) / 2); //将 $arr 平分为 $arr[$start - $mid] 和 $arr[$mid+1 - $end]
        MSort($arr, $start, $mid); //将 $arr[$start - $mid] 归并为有序的$arr[$start - $mid]
        MSort($arr, $mid + 1, $end); //将 $arr[$mid+1 - $end] 归并为有序的 $arr[$mid+1 - $end]
        Merge($arr, $start, $mid, $end); //将$arr[$start - $mid]部分和$arr[$mid+1 - $end]部分合并起来成为有序的$arr[$start - $end]
    }
}

//归并操作
function Merge(array &$arr, $start, $mid, $end)
{
    $i       = $start;
    $j       = $mid + 1;
    $k       = $start;
    $temparr = array();

    while ($i != $mid + 1 && $j != $end + 1) {
        if ($arr[$i] >= $arr[$j]) {
            $temparr[$k++] = $arr[$j++];
        } else {
            $temparr[$k++] = $arr[$i++];
        }
    }

    //将第一个子序列的剩余部分添加到已经排好序的 $temparr 数组中
    while ($i != $mid + 1) {
        $temparr[$k++] = $arr[$i++];
    }
    //将第二个子序列的剩余部分添加到已经排好序的 $temparr 数组中
    while ($j != $end + 1) {
        $temparr[$k++] = $arr[$j++];
    }
    for ($i = $start; $i <= $end; $i++) {
        $arr[$i] = $temparr[$i];
    }
}

$arr = array(9, 1, 5, 8, 3, 7, 4, 6, 2);
MergeSort($arr);
var_dump($arr);
