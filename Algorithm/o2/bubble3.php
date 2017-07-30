<?php

//交换方法
function swap(array &$arr, $a, $b)
{
    $temp    = $arr[$a];
    $arr[$a] = $arr[$b];
    $arr[$b] = $temp;
}
//冒泡排序的优化(如果某一次循环的时候没有发生元素的交换，则整个数组已经是有序的了)
function BubbleSort1(array &$arr)
{
    $length = count($arr);
    $flag   = true;

    for ($i = 0; ($i < $length - 1) && $flag; $i++) {
        $flag = false;
        for ($j = $length - 2; $j >= $i; $j--) {
            if ($arr[$j] > $arr[$j + 1]) {
                swap($arr, $j, $j + 1);
                $flag = true;

            }
        }
    }
}

$arr = array(9, 1, 5, 8, 3, 7, 4, 6, 2);
BubbleSort($arr);
print_r($arr);
