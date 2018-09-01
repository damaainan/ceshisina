<?php

// 顺序统计、中值算法，时间复杂度n

$a = [2, 1, 3, 5, 4];

function random_partition_algorithm($arr, $position)
{
    if (count($arr) < 2) {
        return $arr;
    }

    $x = $arr[0];

    $left_arr = [];
    $right_arr = [];

    foreach($arr as $k => $v) {
        if ($k) {
            if ($v <= $x) {
                $left_arr[] = $v;
            } else {
                $right_arr[] = $v;
            }
        }
    }

    $middle_index = count($left_arr) + 1;
    if ($middle_index == $position) {
        return [$x];
    }
    if ($middle_index < $position) {
        $new_position = $position - $middle_index;
        return random_partition_algorithm($right_arr, $new_position);
    }

    return random_partition_algorithm($left_arr, $position);
}

var_dump(random_partition_algorithm($a, 3));

/**
 * Output:
 * array(1) {
 *   [0]=>
 *     int(3)
 * }
 */