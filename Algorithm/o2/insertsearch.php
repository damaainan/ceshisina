<?php
    
//插值查找(前提是数组必须是有序数组) 事件复杂度　O(logn)
//但对于数组长度比较大，关键字分布又是比较均匀的来说，插值查找的效率比折半查找的效率高

$i = 0;    //存储对比的次数

//@param 待查找数组
//@param 待搜索的数字
function insertsearch($arr,$num){
    $count = count($arr);
    $lower = 0;
    $high = $count - 1;
    global $i;

    while($lower <= $high){

        $i ++; //计数器

        if($arr[$lower] == $num){
            return $lower;
        }
        if($arr[$high] == $num){
            return $high;
        }

        // 折半查找 ： $middle = intval(($lower + $high) / 2);
        $middle = intval($lower + ($num - $arr[$lower]) / ($arr[$high] - $arr[$lower]) * ($high - $lower)); 
        if($num < $arr[$middle]){
            $high = $middle - 1;
        }else if($num > $arr[$middle]){
            $lower = $middle + 1;
        }else{
            return $middle;
        }
    }

    return -1;
}

$arr = array(0,1,16,24,35,47,59,62,73,88,99);
$pos = insertsearch($arr,62);
print($pos);
echo "<br>";
echo $i;