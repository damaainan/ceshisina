<?php
/**
 *题目描述

程序设计：给定2个大小分别为n, m的整数集合，分别存放在两个数组中 int A[n], B[m]，输出两个集合的交集。
 */

function deal($arr1, $arr2) {
    $len1 = count($arr1);
    $len2 = count($arr2);
    if ($len1 > $len2) {
        $rst = inter($arr1, $arr2);
    } else {
        $rst = inter($arr2, $arr1);
    }

    var_dump($rst);
}
function inter($arr1, $arr2) {
    $rst = [];
    for ($i = 0, $len = count($arr1); $i < $len; $i++) {
        echo $i;
        if (in_array($arr1[$i], $arr2)) {
            $rst[] = $arr1[$i];
        }
    }
    return $rst;
}

$arr1 = [1, 3, 5, 7, 9];
$arr2 = [2, 4, 6, 5, 8, 10, 3];
deal($arr1, $arr2);