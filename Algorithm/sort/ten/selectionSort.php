<?php 
header("Content-type:text/html; Charset=utf-8");
$arr = [];

for ($i = 0; $i < 5000; $i++) {
    $arr[] = rand(1, 10000);
}

//3 选择排序
function selectionSort($arr)
{
    $count = count($arr);
    for ($i = 0; $i < $count - 1; $i++) {
        //找到最小的值
        $min = $i; // 假设第一个元素最小 ，和剩余其他元素比较
        for ($j = $i + 1; $j < $count; $j++) {
            //由小到大排列
            if ($arr[$min] > $arr[$j]) {
                //表明当前最小的还比当前的元素大
                $min = $j;
                //赋值新的最小的
            }
        }
        /*swap$array[$i]and$array[$min]即将当前内循环的最小元素放在$i位置上*/
        if ($min != $i) { // 最小值非假设值 交换  $arr[$i] = [ $arr[$min], $arr[$min] = $arr[$i] ][0]
            $temp = $arr[$min];
            $arr[$min] = $arr[$i];
            $arr[$i] = $temp;
        }
    } // 一直循环直到最后一个
    return $arr;

}

$selection_start_time = microtime(true);

$selection_sort = selectionSort($arr);

$selection_end_time = microtime(true);

$selection_need_time = $selection_end_time - $selection_start_time;

print_r("选择排序耗时:" . $selection_need_time . "<br />");

//选择排序耗时:8.803966999054 