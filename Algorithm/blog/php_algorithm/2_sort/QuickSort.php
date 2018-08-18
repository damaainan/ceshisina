<?php

// 快速排序 O(nlogn) 
function quickSort($array)
{
    if(count($array) <= 1)
        return $array;

    $mid = $array[0]; //获取一个用于分割的关键字，一般是首个元素
    $leftArray = array(); 
    $rightArray = array();
    foreach($array as $v)
    {
        if($v > $mid)
            $rightArray[] = $v;  //把比$mid大的数放到一个数组里
        if($v < $mid)
            $leftArray[] = $v;   //把比$mid小的数放到另一个数组里
    }

    $leftArray = quickSort($leftArray); //把比较小的数组再一次进行分割
    $leftArray[] = $mid;        //把分割的元素加到小的数组后面，不能忘了它哦
    $rightArray = quickSort($rightArray);  //把比较大的数组再一次进行分割
    return array_merge($leftArray,$rightArray);  //组合两个结果
}

$arr = [100, 2, 89, 44, 9, 10];
print_r(quickSort($arr));


