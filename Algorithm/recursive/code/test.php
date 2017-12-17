<?php

// PHP 递归效率分析


function qsort(&$arr)
{
    _quick_sort($arr, 0, count($arr) - 1);
}

/**
 * 采用递归算法的快速排序。
 *
 * @param array $arr 要排序的数组
 * @param int $low 最低的排序子段
 * @param int $high 最高的排序字段
 */
function _quick_sort(&$arr, $low, $high)
{
    $low_data  = $arr[$low];
    $prev_low  = $low;
    $prev_high = $high;
    while ($low < $high) {
        while ($arr[$high] >= $low_data && $low < $high) {
            $high--;
        }
        if ($low < $high) {
            $arr[$low] = $arr[$high];
            $low++;
        }
        while ($arr[$low] <= $low_data && $low < $high) {
            $low++;
        }
        if ($low < $high) {
            $arr[$high] = $arr[$low];
            $high--;
        }
    }
    $arr[$low] = $low_data;
    if ($prev_low < $low) {
        _quick_sort($arr, $prev_low, $low);
    }
    if ($low + 1 < $prev_high) {
        _quick_sort($arr, $low + 1, $prev_high);
    }
}

function quickSort(&$arr)
{
    $stack = array();
    array_push($stack, 0);
    array_push($stack, count($arr) - 1);
    while (!empty($stack)) {
        $high      = array_pop($stack);
        $low       = array_pop($stack);
        $low_data  = $arr[$low];
        $prev_low  = $low;
        $prev_high = $high;
        while ($low < $high) {
            while ($arr[$high] >= $low_data && $low < $high) {
                $high--;
            }
            if ($low < $high) {
                $arr[$low] = $arr[$high];
                $low++;
            }
            while ($arr[$low] <= $low_data && $low < $high) {
                $low++;
            }
            if ($low < $high) {
                $arr[$high] = $arr[$low];
                $high--;
            }
        }
        $arr[$low] = $low_data;
        if ($prev_low < $low) {
            array_push($stack, $prev_low);
            array_push($stack, $low);
        }
        if ($low + 1 < $prev_high) {
            array_push($stack, $low + 1);
            array_push($stack, $prev_high);
        }
    }
}

function qsortTest1()
{
    $arr = range(1, 1000);
    shuffle($arr);
    $arr2 = $arr;
    $t1   = microtime(true);
    quickSort($arr2);
    $t2 = microtime(true) - $t1;
    echo "非递归调用的花费：" . $t2 . "\r\n";
    $arr1 = $arr;
    $t1   = microtime(true);
    qsort($arr1);
    $t2 = microtime(true) - $t1;
    echo "递归调用的花费：" . $t2 . "\r\n";
}

qsortTest1();