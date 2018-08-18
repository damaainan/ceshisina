<?php

//希尔排序(对直接插入排序的改进)
// 暂不明白

function ShellSort(array &$arr)
{
    $count = count($arr);
    $inc   = $count; //增量
    do {
        //计算增量
        //$inc = floor($inc / 3) + 1;
        $inc = ceil($inc / 2);
        for ($i = $inc; $i < $count; $i++) {
            $temp = $arr[$i]; //设置哨兵
            //需将$temp插入有序增量子表
            for ($j = $i - $inc; $j >= 0 && $arr[$j + $inc] < $arr[$j]; $j -= $inc) {
                $arr[$j + $inc] = $arr[$j]; //记录后移
            }
            //插入
            $arr[$j + $inc] = $temp;
        }
        //增量为1时停止循环
    } while ($inc > 1);
}

//$arr = array(9,1,5,8,3,7,4,6,2);
$arr = array(49, 38, 65, 97, 76, 13, 27, 49, 55, 04);
ShellSort($arr);
var_dump($arr);
