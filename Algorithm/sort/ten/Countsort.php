<?php

header("Content-type:text/html; Charset=utf-8");

$arr = [];

for ($i = 0; $i < 5000; $i++) {
    $arr[] = rand(1, 50000);
}

// 9  计数排序

/**
 * 计数排序
 * @param $arr
 * @return mixed
 */
function Countsort2($arr)// 此方法报错 看不明白
{

    $max = $arr[0]; //找出待排序的数组中最大和最小的元素
    $min = $arr[0];

    foreach ($arr as $key => $value) {
        if ($value > $max) {
            $max = $value;
        }
        if ($value < $min) {
            $min = $value;
        }
    }
    //这里k的大小是要排序的数组中，元素大小的极值差+1
    $c = [];
    $k = $max - $min + 1;
    for ($i = 0; $i < count($arr); $i++) {// 计数 
        $c[$arr[$i] - $min] += 1;
    }

    for ($i = 1; $i < count($c); ++$i) { // 这是什么 ？ 
        $c[$i] = $c[$i] + $c[$i - 1];
    }

    for ($i = count($arr); $i > 0; --$i) {
        $b[ --$c[$arr[$i] - $min] ] = $arr[$i];
    }

    return $b;
}


function Countsort(array $numbers=array()) 
    {
        $count = count( $numbers );
        if( $count <= 1 ) return $numbers;

        // 找出待排序的数组中最大值和最小值
        $min = min($numbers);
        $max = max($numbers);

        // 计算待排序的数组中每个元素的个数
        $count_array = array();

        // 可以选择不初始化计数数组 
        // 优化方法 效果很明显
        
        /*
        foreach($numbers as $v){
            if(isset($count_array[$v])){
                $count_array[$v] += 1;
            }else{
                $count_array[$v]=1;
            }
        }
        */
          

        for($i = $min; $i <= $max; $i++) //这里也有问题 如果相差很多 不好处理 
        {
            $count_array[$i] = 0; // 数组 赋值  
        }
        
        foreach($numbers as $v)// 如果有某个元素 则计数加 1 
        {
            $count_array[$v] += 1;
        }

// ------------------------

        $ret = array();
        foreach ($count_array as $k=>$c) // 不放心可以根据键值再排一次序
        {
            for($i = 0; $i < $c; $i++) //根据次数将 $c 个 元素 $k 加入数组 
            {
                $ret[] = $k;
            }
        }
        return $ret;
    }


function countingSort($arr)
{

    $length = count($arr);
    if ($length <= 1) {
        return $arr;
    }

    $size = count($arr);
    $max  = $arr[0];

    //找出数组中最大的数
    for ($i = 1; $i < $size; $i++) {
        if ($max < $arr[$i]) {
            $max = $arr[$i];
        }

    }

    //初始化用来计数的数组
    for ($i = 0; $i <= $max; $i++) { // 这里是个bug 
        $count_arr[$i] = 0;
    }

    //对计数数组中键值等于$arr[$i]的加1
    for ($i = 0; $i < $size; $i++) {
        $count_arr[$arr[$i]]++;
    }

    //相邻的两个值相加
    for ($i = 1; $i <= $max; $i++) {
        $count_arr[$i] = $count_arr[$i - 1] + $count_arr[$i];
    }

    //键与值翻转
    for ($i = $size - 1; $i >= 0; $i--) {
        $over_turn[$count_arr[$arr[$i]]] = $arr[$i];
        $count_arr[$arr[$i]]--; // 前一个数找到位置后，那么和它值相同的数位置往前一步
    }

    //按照顺序排列
    $result = array();
    for ($i = 1; $i <= $size; $i++) {
        array_push($result, $over_turn[$i]);
    }

    return $result;
}

$countsort_start_time = microtime(true);

// $countsort_sort = countingSort($arr);
$countsort_sort = Countsort($arr);

$countsort_end_time = microtime(true);

$countsort_need_time = $countsort_end_time - $countsort_start_time;

print_r("计数排序耗时:" . $countsort_need_time . "<br />");


// 计数排序耗时:0.058456897735596
// 
// 计数数组不提前初始化之后得到优化
// 计数排序耗时:0.010740995407104