<?php

// 插入排序 O(n2) 
function insertSort($arr){
    $count = count($arr);

    for ($i = 1; $i < $count ; $i++) { 
        $temp = $arr[$i];
        for ($j = $i - 1; $j >= 0 ; $j--) { 
            if ($arr[$j] > $temp) {
                $arr[$j + 1] = $arr[$j];
                $arr[$j] = $temp;
            }
        }
    }
    
    return $arr;
}

$arr = [100, 2, 89, 44, 9, 10];
print_r(insertSort($arr));


