<?php 

function countingSort($array) {
    $len = count($array);
        $B = [];
        $C = [];
        $min = $max = $array[0];
    // print_f('计数排序耗时');
    for ($i = 0; $i < $len; $i++) {
        $min = $min <= $array[$i] ? $min : $array[$i];
        $max = $max >= $array[$i] ? $max : $array[$i];
        $C[$array[$i]] = $C[$array[$i]] ? $C[$array[$i]] + 1 : 1;
    }
    for ($j = $min; $j < $max; $j++) {
        $C[$j + 1] = ($C[$j + 1] || 0) + ($C[$j] || 0);
    }
    for ($k = $len - 1; $k >= 0; $k--) {
        $B[$C[$array[$k]] - 1] = $array[$k];
        $C[$array[$k]]--;
    }
    // print_f('计数排序耗时');
    return $B;
}
$arr = [2, 2, 3, 8, 7, 1, 2, 2, 2, 7, 3, 9, 8, 2, 1, 4, 2, 4, 6, 9, 2];
$aa=countingSort($arr);
var_dump($aa); 