<?php
/**
 * 作者：迹忆
 * 个人博客：迹忆博客
 * 博客url：www.onmpw.com
 * ************
 * 冒泡排序
 * ************
 */
/**
 * 交换函数
 */
function swap(&$arr,$a,$b){
    $t = $arr[$a];
    $arr[$a] = $arr[$b];
    $arr[$b] = $t;
}
function BubbleSort(&$arr){
    $end = count($arr)-1;
    while($end>0){
        $flag = false;
        for($i=0;$i<$end;$i++){
            if($arr[$i]>$arr[$i+1]){
                swap($arr,$i,$i+1);
                $flag = true;
            }
        }
        if(!$flag) break;
        $end--;
    }
}
$arr = array(
    15,77,23,43,90,87,68,32,11,22,33,99,88,66,44,113,
    224,765,980,159,456,7,998,451,96,0,673,82,91,100
);
BubbleSort($arr,0,count($arr)-1);
print_r($arr);