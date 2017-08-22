<?php
header("Content-type:text/html; Charset=utf-8");

$arr = [];

for ($i = 0; $i < 5000; $i++) {
    $arr[] = rand(1, 50000);
}

function bucketSort($max, $array)
{
    //填充木桶
    $arr = array_fill(0, $max + 1, 0);

    //开始标示木桶
    for ($i = 0; $i <= count($array) - 1; $i++) {
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

// 优化 1 $max 从数组中直接取 


function bucketSort2($array)
{
    $max=max($array);
    //填充木桶
    $arr = array_fill(0, $max + 1, 0);

    //开始标示木桶
    for ($i = 0; $i <= count($array) - 1; $i++) {
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


 // 优化 2  不提前填充木桶

// 优化 3 从木桶中取数据 不必要循环太多
// 
// 2 和 3 能加快一倍

function bucketSort3($array)
{
    $max=max($array);
    //填充木桶
    // $arr = array_fill(0, $max + 1, 0);

    //开始标示木桶
    for ($i = 0; $i <= count($array) - 1; $i++) {
        if(isset($arr[$array[$i]])){
            $arr[$array[$i]]++;
        }else{
            $arr[$array[$i]]=1;
        }
    }

    $mutomg = array();


    foreach ($arr as $key => $val) {
        for ($j = 1; $j <= $val; $j++) {
            //这一行主要用来控制输出多个数字
            $mutong[] = $key;
        }
    }

    return $mutong;
}






$countsort_start_time = microtime(true);

$countsort_sort =bucketSort(50000,$arr); // $max 的取值？

$countsort_end_time = microtime(true);

$countsort_need_time = $countsort_end_time - $countsort_start_time;

print_r("桶排序耗时:" . $countsort_need_time . "<br />");


// 桶排序耗时:0.066769123077393

$countsort_start_time = microtime(true);

$countsort_sort =bucketSort2($arr); // $max 的取值？

$countsort_end_time = microtime(true);

$countsort_need_time = $countsort_end_time - $countsort_start_time;

print_r("桶排序2耗时:" . $countsort_need_time . "<br />");


$countsort_start_time = microtime(true);

$countsort_sort =bucketSort3($arr); // $max 的取值？

$countsort_end_time = microtime(true);

$countsort_need_time = $countsort_end_time - $countsort_start_time;

print_r("桶排序3耗时:" . $countsort_need_time . "<br />");

// 桶排序耗时:0.062643051147461
// 桶排序2耗时:0.060757875442505
// 桶排序3耗时:0.023086071014404

