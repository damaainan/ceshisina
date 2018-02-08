<?php
/**
 * 作者：迹忆
 * 个人博客：迹忆博客
 * 博客url：www.onmpw.com
 * ************
 * 希尔排序
 * ************
 */
function ShellSort(&$arr){
    /*
     * 首先初始化 增量  数组长度/2 取整 floor() 函数向下取整  对于增量每次循环都由 当前增量/2
     */
    for($dl=floor(count($arr)/2);$dl>=1;$dl=floor($dl/2)){
        /*
         * 每次从 增量的位置开始，直到数组递增变量达到数组的长度
         */
        for($j=$dl;$j<count($arr);$j++){
            for($i=$j-$dl;$i>=0;$i-=$dl){
                if($arr[$i+$dl]<$arr[$i]){
                    $temp = $arr[$i+$dl];
                    $arr[$i+$dl]=$arr[$i];
                    $arr[$i]=$temp;
                }
            }
        }
    }
}
$arr = array(
    15,77,23,43,90,87,68,32,11,22,33,99,88,66,44,113,
    224,765,980,159,456,7,998,451,96,0,673,82,91,100
);
ShellSort($arr);
print_r($arr);