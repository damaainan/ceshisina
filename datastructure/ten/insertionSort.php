<?php 
header("Content-type:text/html; Charset=utf-8");
$arr = [];

for ($i = 0; $i < 5000; $i++) {
    $arr[] = rand(1, 10000);
}


//1 插入排序


function insertionSort($arr)
{

    for ($i = 1; $i < count($arr); $i++) {
        $tmp = $arr[$i]; //设置监视哨
        $key = $i - 1; //设置开始查找的位置
        while ($key >= 0 && $tmp < $arr[$key]) { // 监视哨的值比查找的值小 并且 没有到此次查询的第一个
            $arr[$key + 1] = $arr[$key];  //数组的值进行后移
            $key--;  //要查找的位置后移
        }
        if (($key + 1) != $i) //放置监视哨
            $arr[$key + 1] = $tmp;
    }
    return $arr;

}

$insertion_start_time = microtime(true);

$insertion_sort = insertionSort($arr);

$insertion_end_time = microtime(true);

$insertion_need_time = $insertion_end_time - $insertion_start_time;

print_r("插入排序耗时:" . $insertion_need_time . "<br />");
