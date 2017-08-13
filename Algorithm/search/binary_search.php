<?php

// 二分查找算法，时间复杂度lgn

$a = [1, 2, 3, 4, 5, 6, 7];

function binary_search($arr, $needle)
{
    if (!$arr) {
        return null;
    }

    $cnt_arr = count($arr);
    $index_start = each($arr)['key'];
    $index_stop = $index_start + $cnt_arr - 1;

    if ($cnt_arr % 2 != 0) {
        $middle_index = ($index_start + $index_stop) / 2;
    } else {
        $middle_index = floor(($index_start + $index_stop) / 2);
    }

    if ($needle == $arr[$middle_index]) {
        return $middle_index;
    } else {
        if ($needle < $arr[$middle_index]) {
            $new_arr = array_slice($arr, $index_start, $middle_index - $index_start, true);
        } else {
            $new_arr = array_slice($arr, $middle_index + 1, $index_stop - $middle_index, true);
        }
        return binary_search($new_arr, $needle);
    }
}

var_dump(binary_search($a, 6));

/**
 * Output:
 * 5
 */