<?php
/**
 * 作者：迹忆
 * 个人博客：迹忆博客
 * 博客url：www.onmpw.com
 * ************
 * 折半插入排序
 * ************
 */
function HalfInsertSort(&$arr){
    for($i=1;$i<count($arr);$i++){
        if($arr[$i]<$arr[$i-1]){
            //使用二分查找法查找适当的位置
            $low = 0;
            $high = $i-1;
            $pos = floor(($low+$high)/2);
            $key = $arr[$i];
            while($low<$high){
                if($arr[$pos]>$key){
                    $high = $pos-1;
                }elseif($arr[$pos]<=$key){
                    $low = $pos+1;
                }
                $pos = ceil(($low+$high)/2);
            }
            //二分查找法结束
            if($arr[$pos]>$arr[$i]){
                $pos = $pos-1;
            }
            for($j=$i-1;$j>$pos;$j--){
                $arr[$j+1]=$arr[$j];
            }
            $arr[$j+1] = $key;
        }
    }
}
$arr = array(
    15,77,23,43,90,87,68,32,11,22,33,99,88,66,44,113,
    224,765,980,159,456,7,998,451,96,0,673,82,91,100
);
HalfInsertSort($arr);
print_r($arr); 