<?php
function binarySearch($arr, $target)
{
    if (!$arr || empty($arr)) {
        return -1;
    }

    $low  = 0;
    $high = count($arr) - 1;
    while ($low < $high) {
        $middle = intval(($low + $high) / 2);
        if ($target < $arr[$middle]) {
            $high = $middle - 1;
        } else if ($target > $arr[$middle]) {
            $low = $middle + 1;
        } else {
            return $middle;
        }
    }
    return -1;
}
$arr = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10);
print(binarySearch($arr, 8));
