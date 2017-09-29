<?php 
header("Content-type:text/html; Charset=utf-8");
/**
 * PHP随机合并数组并保持原排序

# 场景

原有帖子列表A，现需在A中推广新业务B，则需要在A列表中1:1混合B的数据，随机混合，但需保持A和B两列表原来的数据排序。具体参考下面示例的效果。

# 原理

1. 获知总共元素数量N；
1. for循环N次，取随机数；
1. 根据随机数依次从头获取A或B的值，推入新数组中；
 */


//随机合并两个数组元素，保持原有数据的排序不变（即各个数组的元素在合并后的数组中排序与自身原来一致）
function shuffleMergeArray($array1,$array2) {
    $mergeArray = array();
    $sum = count($array1) + count($array2);
    for ($k = $sum; $k > 0; $k--) {
        $number = mt_rand(1, 2);
        if ($number == 1) {
            $mergeArray[] = $array2 ? array_shift($array2) : array_shift($array1);
        } else {
            $mergeArray[] = $array1 ? array_shift($array1) : array_shift($array2);
        }
    }


    return $mergeArray;
}

// 合并前的数组：
$array1 = array(1, 2, 3, 4);
$array2 = array('a', 'b', 'c', 'd', 'e');

// 合并后的数据：
/*    $mergeArray = array (
  0 => 'a',
  1 => 1,
  2 => 'b',
  3 => 2,
  4 => 'c',
  5 => 'd',
  6 => 3,
  7 => 4,
  8 => 'e',
)*/

$mergeArray=shuffleMergeArray($array1,$array2)