<?php

// 直接选择排序 O(n2) 比冒泡排序交换次数少
function straightSelectionSort($arr) {
    $tmp = 0;
    $count = count($arr);
    $k = 0;
    for ($i = 0; $i < $count - 1; $i++) { 
        $k = $i;
        for ($j = $i; $j < $count; $j++) { 
            if ($arr[$j] < $arr[$k]) {
                $k = $j;
            }
        }

        // 进行交换
        $tmp = $arr[$i];
        $arr[$i] = $arr[$k];
        $arr[$k] = $tmp;
    }
    return $arr;
}

$arr = [100, 2, 89, 44, 9, 10];
print_r(straightSelectionSort($arr));


