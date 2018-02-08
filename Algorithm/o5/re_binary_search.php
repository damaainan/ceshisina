<?php 

//递归实现折半查找

function re_binary_search($arr,$target,$height,$low=0){
    if($height < $low || $arr[$low] > $target || $arr[$height] < $target){
        return -1;
    }

    $mid = intval(($low+$height)/2);
    if($arr[$mid] > $target){//前半段
        return re_binary_search($arr,$target,$mid-1,$low);
    }
    if($arr[$mid] < $target){//后半段
        return re_binary_search($arr,$target,$height,$mid+1);
    }
    return $mid;
}

$item = array(50, 30, 20,35,33,40,36, 100, 56, 78);
var_dump(re_binary_search($item,'8',count($item)-1));
var_dump($item[re_binary_search($item,'8',count($item)-1)]);