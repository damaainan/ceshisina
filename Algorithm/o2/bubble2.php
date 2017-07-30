<?php

//交换方法
function swap(array &$arr, $a, $b)
{
    $temp    = $arr[$a];
    $arr[$a] = $arr[$b];
    $arr[$b] = $temp;
}
//冒泡排序
function BubbleSort(array &$arr)
{
    $length = count($arr);
    for ($i = 0; $i < $length - 1; $i++) {
        //从后往前逐层上浮小的元素
        for ($j = $length - 2; $j >= $i; $j--) {
            //两两比较相邻记录
            if ($arr[$j] > $arr[$j + 1]) {
                swap($arr, $j, $j + 1);
            }
        }
    }
}

$arr = array(9, 1, 5, 8, 3, 7, 4, 6, 2);
BubbleSort($arr);
print_r($arr);
