<?php
function bucketSort($max, $array)
{
    //填充木桶
    $arr = array_fill(0, $max + 1, 0);

    //开始标示木桶
    for ($i = 0, $len = count($array); $i <= $len - 1; $i++) {
        $arr[$array[$i]]++;
    }

    $mutomg = array();
    //开始从木桶中拿出数据
    for ($i = 0; $i <= $max; $i++) {
        for ($j = 1; $j <= $arr[$i]; $j++) {
            //这一行主要用来控制输出多个数字
            $mutong[] = $i;
        }
    }
    return $mutong;
}

$arr = [];
for ($i = 0; $i < 50000; $i++) {
    $arr[] = rand(1, 100000);
}

$start_time = microtime(true);

$sm = memory_get_usage(true);

$sort = bucketSort(100000, $arr);
$em = memory_get_usage(true);

$use_memory = $em - $sm;
print_r("排序 占 用 内存:" . $use_memory . "\r\n");
print_r("排序占用峰值内存:" . memory_get_peak_usage(true) . "\r\n");

$end_time = microtime(true);
$need_time = $end_time - $start_time;

print_r("排序耗时:" . $need_time . "\r\n");