<?php

//八大排序算法的 PHP 实现 和 效率测试
//

/*

insert
1       0.43596506118774
2       0.41342520713806
3       0.35684704780579
shell
1       0.034051895141602
2       0.027128934860229
3       0.027184963226318
bubble
1       0.5653920173645
2       0.59774017333984
3       0.48480916023254
quick
1       0.039077043533325
2       0.038660049438477
3       0.041055917739868
select
1       0.3118588924408
2       0.32860994338989
3       0.39379620552063
heap
1       0.26264595985413
2       0.21713304519653
3       0.21712112426758
merge
1       0.10475611686707
2       0.10028290748596
3       0.098167896270752
radix
1       0.03685998916626
2       0.028564929962158
3       0.027681112289429

 */

$num = 1000;
$times = 3;
$algorithms = array('insert', 'shell', 'bubble', 'quick', 'select', 'heap', 'merge', 'radix');
$arr = array();
while (count($arr) < $num) {
    $key = rand(1, $num * 10);
    $arr[$key] = 1;
}
$arr = array_keys($arr);
$arrOk = array();
//$arr = [6, 15, 41, 49, 69, 40, 45, 91, 46, 13];
//echo 'Origin: ' . "\t" . implode(', ', $arr) . PHP_EOL;
foreach ($algorithms as $algorithm) {
    echo $algorithm . PHP_EOL;
    $func = $algorithm . '_sort';
    for ($i = 1; $i <= $times; $i++) {
        echo $i . "\t";
        $startTime = microtime(true);
        $_tmpArr = $arr;
        $arr2 = $func($_tmpArr);
        if (empty($arrOk)) {
            $arrOk = $arr2;
        }
        if ($arrOk != $arr2) {
            echo 'Error: ' . PHP_EOL;
            echo 'Origin: ' . "\t" . implode(', ', $arr) . PHP_EOL;
            echo 'Right:' . "\t" . implode(', ', $arrOk) . PHP_EOL;
            echo 'Error:' . "\t" . implode(', ', $arr2) . PHP_EOL;
        }
        $endTime = microtime(true);
        echo ($endTime - $startTime) . PHP_EOL;
    }
    //sleep(1);
}
echo PHP_EOL;
//echo implode(', ', $arrOk) . PHP_EOL;
/** ========================================= */
/**
 * 插入排序
 *
 * @param array $lists
 * @return array
 */
function insert_sort(array $lists)
{
    for ($i = 1; $i < count($lists); $i++) {
        $key = $lists[$i];
        for ($j = $i - 1; $j >= 0 && $lists[$j] > $key; $j--) {
            $lists[$j + 1] = $lists[$j];
            $lists[$j] = $key;
        }
    }
    return $lists;
}
/**
 * 希尔排序 标准
 *
 * @param array $lists
 * @return array
 */
function shell_sort(array $lists)
{
    $n = count($lists);
    $step = 2;
    $gap = intval($n / $step);
    while ($gap > 0) {
        for ($gi = 0; $gi < $gap; $gi++) {
            for ($i = $gi; $i < $n; $i += $gap) {
                $key = $lists[$i];
                for ($j = $i - $gap; $j >= 0 && $lists[$j] > $key; $j -= $gap) {
                    $lists[$j + $gap] = $lists[$j];
                    $lists[$j] = $key;
                }
            }
        }
        $gap = intval($gap / $step);
    }
    return $lists;
}
/**
 * 冒泡排序
 *
 * @param array $lists
 * @return array
 */
function bubble_sort(array $lists)
{
    $num = count($lists);
    for ($i = 0; $i < $num; $i++) {
        for ($j = $i + 1; $j < $num; $j++) {
            if ($lists[$i] > $lists[$j]) {
                $key = $lists[$i];
                $lists[$i] = $lists[$j];
                $lists[$j] = $key;
            }
        }
    }
    return $lists;
}
/**
 * 快速排序
 *
 * @param array $lists
 * @param $left
 * @param $right
 * @return array
 */
function quick_sort(array &$lists, $left = 0, $right = null)
{
    if (is_null($right)) {
        $right = count($lists) - 1;
    }
    if ($left >= $right) {
        return $lists;
    }
    $key = $lists[$left];
    $low = $left;
    $high = $right;
    while ($left < $right) {
        while ($left < $right && $lists[$right] > $key) {
            $right--;
        }
        $lists[$left] = $lists[$right];
        while ($left < $right && $lists[$left] < $key) {
            $left++;
        }
        $lists[$right] = $lists[$left];
    }
    $lists[$right] = $key;
    quick_sort($lists, $low, $left - 1);
    quick_sort($lists, $left + 1, $high);
    return $lists;
}
/**
 * 直接选择排序
 *
 * @param array $lists
 * @return array
 */
function select_sort(array $lists)
{
    $n = count($lists);
    for ($i = 0; $i < $n; $i++) {
        $key = $i;
        for ($j = $i + 1; $j < $n; $j++) {
            if ($lists[$j] < $lists[$key]) {
                $key = $j;
            }
        }
        $val = $lists[$key];
        $lists[$key] = $lists[$i];
        $lists[$i] = $val;
    }
    return $lists;
}
/**
 * 堆排序
 *
 * @param array $lists
 * @return array
 */
function heap_sort(array $lists)
{
    $n = count($lists);
    build_heap($lists);
    while (--$n) {
        $val = $lists[0];
        $lists[0] = $lists[$n];
        $lists[$n] = $val;
        heap_adjust($lists, 0, $n);
        //echo "sort: " . $n . "\t" . implode(', ', $lists) . PHP_EOL;
    }
    return $lists;
}
function build_heap(array &$lists)
{
    $n = count($lists) - 1;
    for ($i = floor(($n - 1) / 2); $i >= 0; $i--) {
        heap_adjust($lists, $i, $n + 1);
        //echo "build: " . $i . "\t" . implode(', ', $lists) . PHP_EOL;
    }
    //echo "build ok: " . implode(', ', $lists) . PHP_EOL;
}
function heap_adjust(array &$lists, $i, $num)
{
    if ($i > $num / 2) {
        return;
    }
    $key = $i;
    $leftChild = $i * 2 + 1;
    $rightChild = $i * 2 + 2;
    if ($leftChild < $num && $lists[$leftChild] > $lists[$key]) {
        $key = $leftChild;
    }
    if ($rightChild < $num && $lists[$rightChild] > $lists[$key]) {
        $key = $rightChild;
    }
    if ($key != $i) {
        $val = $lists[$i];
        $lists[$i] = $lists[$key];
        $lists[$key] = $val;
        heap_adjust($lists, $key, $num);
    }
}
/**
 * 归并排序
 *
 * @param array $lists
 * @return array
 */
function merge_sort(array $lists)
{
    $n = count($lists);
    if ($n <= 1) {
        return $lists;
    }
    $left = merge_sort(array_slice($lists, 0, floor($n / 2)));
    $right = merge_sort(array_slice($lists, floor($n / 2)));
    $lists = merge($left, $right);
    return $lists;
}
function merge(array $left, array $right)
{
    $lists = [];
    $i = $j = 0;
    while ($i < count($left) && $j < count($right)) {
        if ($left[$i] < $right[$j]) {
            $lists[] = $left[$i];
            $i++;
        } else {
            $lists[] = $right[$j];
            $j++;
        }
    }
    $lists = array_merge($lists, array_slice($left, $i));
    $lists = array_merge($lists, array_slice($right, $j));
    return $lists;
}
/**
 * 基数排序
 *
 * @param array $lists
 * @return array
 */
function radix_sort(array $lists)
{
    $radix = 10;
    $max = max($lists);
    $k = ceil(log($max, $radix));
    if ($max == pow($radix, $k)) {
        $k++;
    }
    for ($i = 1; $i <= $k; $i++) {
        $newLists = array_fill(0, $radix, []);
        for ($j = 0; $j < count($lists); $j++) {
            $key = $lists[$j] / pow($radix, $i - 1) % $radix;
            $newLists[$key][] = $lists[$j];
        }
        $lists = [];
        for ($j = 0; $j < $radix; $j++) {
            $lists = array_merge($lists, $newLists[$j]);
        }
    }
    return $lists;
}