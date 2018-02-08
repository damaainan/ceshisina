<?php
/**
 * 作者：迹忆
 * 个人博客：迹忆博客
 * 博客url：www.onmpw.com
 * ************
 * 快速排序
 * 其中包括两种实现方式 一种是递归方式  一种是栈的非递归方式
 * ************
 */
function FindPiv(&$arr,$s,$e){
    $p = $s; //基准起始位置
    $v = $arr[$p];  //将数组的第一个值作为基准值
    while($s<$e){
        while($arr[$e]>$v&&$e>$p){
            $e--;
        }
        $arr[$p] = $arr[$e];
        $p = $e;
        while($arr[$s]<$v&&$s<$p){
            $s++;
        }
        $arr[$p] = $arr[$s];
        $p = $s;
    }
    $arr[$p] = $v;
    return $p;
}
/**
 * 快速排序——递归方式
 */
function FastSortRecurse(&$arr,$s,$e){
    if($s>=$e) return ;
    $nextP = FindPiv($arr,$s,$e);  //找到下一个基准所在位置
    FastSortRecurse($arr,$s,$nextP-1);
    FastSortRecurse($arr,$nextP+1,$e);
}
/**
 * 快速排序——非递归方式
 */
function FastSort(&$arr){
    $stack = array();
    array_push($stack,array(0,count($arr)-1));
    while(count($stack)>0){
        $temp = array_pop($stack);
        $p = FindPiv($arr, $temp[0], $temp[1]);
        if($p+1<$temp[1]) array_push($stack,array($p+1,$temp[1]));
        if($temp[0]<$p-1) array_push($stack,array($temp[0],$p-1));
    }
}
$arr = array(
    15,77,23,43,90,87,68,32,11,22,33,99,88,66,44,113,
    224,765,980,159,456,7,998,451,96,0,673,82,91,100
);
FastSort($arr,0,count($arr)-1);
print_r($arr);