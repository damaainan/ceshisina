<?php

//简单选择排序
//
//
//择排序是固定位置，找元素，然后交换。***************

//交换函数
function swap(array &$arr, $a, $b)
{
    $temp    = $arr[$a];
    $arr[$a] = $arr[$b];
    $arr[$b] = $temp;
}
//简单选择排序算法
function SelectSort(array &$arr)
{
    $count = count($arr);
    for ($i = 0; $i < $count - 1; $i++) {
        //记录第$i个元素后的所有元素最小值下标
        $min = $i;
        for ($j = $i + 1; $j < $count; $j++) {
            if ($arr[$j] < $arr[$min]) {
                $min = $j;
            }
        }

        if ($min != $i) {
            swap($arr, $min, $i);
        }
    }
}
$arr = array(9, 1, 5, 8, 3, 7, 4, 6, 2);
SelectSort($arr);
var_dump($arr);
