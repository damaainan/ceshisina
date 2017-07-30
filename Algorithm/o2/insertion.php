<?php

//直接插入排序

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

$arr = array(9, 1, 5, 8, 3, 7, 4, 6, 2);
InsertSort($arr);
var_dump($arr);