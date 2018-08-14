<?php

// 冒泡排序 O(n2)
function bubbleSort($arr) {
    $tmp = 0;
    $count = count($arr);
    for ($i = 0; $i < $count; $i++) { 
        for ($j = $count - 1; $j > $i; $j--) { 
            if ($arr[$j] < $arr[$j - 1]) {
                // 进行交换
                $tmp = $arr[$j];
                $arr[$j] = $arr[$j - 1];
                $arr[$j - 1] = $tmp;
            }
        }
    }
    return $arr;
}

$arr = [100, 2, 89, 44, 9, 10, -4];
print_r(bubbleSort($arr));


