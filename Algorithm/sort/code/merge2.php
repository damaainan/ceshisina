<?php
$arrStoreList = array(3, 2, 4, 1, 5);
$arrStoreList = [];

for ($i = 0; $i < 5000; $i++) {
    $arrStoreList[] = rand(1, 10000);
}
$start_time = microtime(true);
$sort         = new Merge_sort();
$sort->stableSort($arrStoreList, function ($a, $b) {
    // function ($a, $b)匿名函数
    return $a < $b;
});

//静态调用方式也行
/* 
Merge_sort:: stableSort($arrStoreList, function ($a, $b) {
    return $a < $b;
});
*/
$end_time = microtime(true);

$need_time = $end_time - $start_time;

print_r("排序耗时:" . $need_time . "\r\n");

print_r($arrStoreList);

class Merge_sort
{

    public static function stableSort(&$array, $cmp_function = 'strcmp')
    {
        //使用合并排序
        self::mergeSort($array, $cmp_function);
        return;
    }
    private static function mergeSort(&$array, $cmp_function = 'strcmp')
    {
        // Arrays of size < 2 require no action.
        if (count($array) < 2) {
            return;
        }
        // 切分数组
        $halfway = count($array) / 2;
        $array1  = array_slice($array, 0, $halfway);
        $array2  = array_slice($array, $halfway);
        // Recurse to sort the two halves
        self::mergeSort($array1, $cmp_function);
        self::mergeSort($array2, $cmp_function);
        // If all of $array1 is <= all of $array2, just append them.
        
        //array1 与 array2 各自有序;要整体有序，需要比较array1的最后一个元素和array2的第一个元素大小
        if (call_user_func($cmp_function, end($array1), $array2[0]) < 1) {
            $array = array_merge($array1, $array2);
            return;
        }
        // 将两个有序数组合并为一个有序数组：Merge the two sorted arrays into a single sorted array
        $array = array();
        $ptr1  = $ptr2  = 0;
        while ($ptr1 < count($array1) && $ptr2 < count($array2)) {
            if (call_user_func($cmp_function, $array1[$ptr1], $array2[$ptr2]) < 1) {
                $array[] = $array1[$ptr1++];
            } else {
                $array[] = $array2[$ptr2++];
            }
        }
        // Merge the remainder
        while ($ptr1 < count($array1)) {
            $array[] = $array1[$ptr1++];
        }
        while ($ptr2 < count($array2)) {
            $array[] = $array2[$ptr2++];
        }
        return;
    }
}
