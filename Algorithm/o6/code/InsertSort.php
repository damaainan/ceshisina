<?php
/**
 * 作者：迹忆
 * 个人博客：迹忆博客
 * 博客url：www.onmpw.com
 * ************
 * 直接插入排序
 * ************
 */
function InsertSort(&$arr){
    for($i=1;$i<count($arr);$i++){
        $p = $arr[$i];
        for($j=$i-1;$j>=0;$j--){
            if($arr[$j]>$p){
                $arr[$j+1] = $arr[$j];
            }else{
                break;
            }
        }
        $arr[$j+1] = $p;
    }
}
$arr = array(
    15,77,23,43,90,87,68,32,11,22,33,99,88,66,44,113,
    224,765,980,159,456,7,998,451,96,0,673,82,91,100
);
InsertSort($arr);
print_r($arr); 