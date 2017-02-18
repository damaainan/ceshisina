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
function Countsort($arr){

    $max = $arr[0];
    $min = $arr[0];

    foreach($arr as $key => $value) {
        if ($value > $max) {
            $max = $value;
        }
        if ($value < $min) {
            $min = $value;
        }
    }
        //这里k的大小是要排序的数组中，元素大小的极值差+1
        $c=[];
        $k = $max - $min + 1;
        for($i = 0; $i < count($arr) ; $i ++){
            $c[$arr[$i] - $min ] +=1;
        }

        for($i=1;$i < count($c); ++$i){
            $c[$i] = $c[$i] + $c[$i - 1];
        }

        for($i = count($arr);$i > 0 ; --$i){
            $b[ -- $c[$arr[$i] - $min] ] = $arr[$i];
        }

    return $b;
}



$countsort_start_time = microtime(true);

$countsort_sort = countingSort($arr);

$countsort_end_time = microtime(true);

$countsort_need_time = $countsort_end_time - $countsort_start_time;

print_r("计数排序耗时:" . $countsort_need_time . "<br />");