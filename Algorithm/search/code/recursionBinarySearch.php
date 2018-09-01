<?php 
function recursionBinarySearch($arr, $target, $low, $high) {
    if ($low > $high) { return -1; }
    $middle = intval(($low + $high) / 2);
    $crt_value = $arr[$middle];
    if ($crt_value > $target) {
        return recursionBinarySearch($arr, $target, $low, $middle-1);
    }else if ($crt_value < $target) {
        return recursionBinarySearch($arr, $target, $middle+1, $high);
    }else{
        return $middle;
    }
}

$arr = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10);
print(recursionBinarySearch($arr, 8, 0, 9));